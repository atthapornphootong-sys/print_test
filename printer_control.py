"""
===============================================
ระบบควบคุมเครื่องปริ้นอัจฉริยะ - Production Version
Smart Printer Control System
===============================================
Version: 2.1
- ดึง points_per_page จาก API แทน hardcode
- รองรับระบบติดหนี้ (Debt System)
"""

import win32print
import win32api
import time
import tkinter as tk
from tkinter import messagebox, ttk
import json
import requests
from datetime import datetime
import threading
import logging
import os

# ตั้งค่า Logging
log_file = os.path.join(os.path.dirname(__file__), 'printer_control.log')
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(log_file, encoding='utf-8'),
        logging.StreamHandler()
    ]
)

# ===================================================
# การตั้งค่าระบบ - แก้ไขตรงนี้ตามความต้องการ
# ===================================================
class Config:
    # ======================================================
    # แก้ไข API_BASE_URL ให้ตรงกับ server จริง
    # ======================================================
    API_BASE_URL    = "https://dsit.thonburi-u.ac.th/print_test/config/controller.php"
    WAIT_PAGE_TIMEOUT  = 15
    MONITOR_INTERVAL   = 2
    DEFAULT_PRINTER    = None  # None = ใช้ default printer ของ Windows
    PRINTER_ID         = 1

    # ค่าเหล่านี้จะถูก override จาก API ตอน startup และ refresh ก่อนทุก job
    POINTS_PER_PAGE = 1
    DEBT_LIMIT      = 0   # 0 = ไม่อนุญาตติดหนี้
    ALLOW_DEBT      = False # โหลดจาก DB (allow_debt setting)

# ===================================================
# API Client
# ===================================================
class PrintAPI:
    @staticmethod
    def call_api(data):
        try:
            response = requests.post(
                Config.API_BASE_URL,
                json=data,
                timeout=5,
                headers={'Content-Type': 'application/json'}
            )
            return response.json()
        except requests.RequestException as e:
            logging.error(f"API Error: {e}")
            return {'success': False, 'message': str(e)}

    @staticmethod
    def load_settings():
        """ดึงค่า settings จาก API และอัปเดต Config"""
        try:
            response = requests.get(
                Config.API_BASE_URL,
                params={'action': 'get_settings'},
                timeout=5
            )
            result = response.json()
            if result.get('success'):
                settings = result.get('data', {})
                Config.POINTS_PER_PAGE = int(settings.get('points_per_page', 1))
                Config.DEBT_LIMIT      = int(settings.get('debt_limit', 0))
                Config.ALLOW_DEBT      = settings.get('allow_debt', '0') in ('1', 1, True)
                logging.info(f"✅ โหลด settings: points_per_page={Config.POINTS_PER_PAGE}, debt_limit={Config.DEBT_LIMIT}, allow_debt={Config.ALLOW_DEBT}")
            else:
                logging.warning("⚠️ โหลด settings ไม่สำเร็จ ใช้ค่า default")
        except Exception as e:
            logging.error(f"❌ โหลด settings ล้มเหลว: {e}")

    @staticmethod
    def check_student(student_id, pages):
        return PrintAPI.call_api({
            'action': 'check_student',
            'student_id': student_id,
            'pages': pages
        })

    @staticmethod
    def record_print(student_id, pages, job_id, allow_debt=False):
        return PrintAPI.call_api({
            'action': 'record_print',
            'student_id': student_id,
            'pages': pages,
            'printer_id': Config.PRINTER_ID,
            'job_id': job_id,
            'allow_debt': allow_debt
        })

    @staticmethod
    def update_printer_status(printer_name, paper_used):
        return PrintAPI.call_api({
            'action': 'update_printer_status',
            'printer_name': printer_name,
            'paper_used': paper_used,
            'status': 'ready'
        })

# ===================================================
# Print Job Class
# ===================================================
class PrintJob:
    def __init__(self, job_id, document_name, pages=None):
        self.job_id        = job_id
        self.document_name = document_name
        self.pages         = pages
        self.timestamp     = datetime.now()

# ===================================================
# Confirmation Dialog
# ===================================================
class PrintConfirmationDialog:
    def __init__(self, print_job, printer_name):
        self.print_job    = print_job
        self.printer_name = printer_name
        self.result       = False
        self.student_id   = None
        self.allow_debt   = False
        self.root         = None

    def show(self):
        self.root = tk.Tk()
        self.root.title("🖨️ ยืนยันการพิมพ์")
        self.root.geometry("520x460")
        self.root.resizable(False, False)
        self.root.eval('tk::PlaceWindow . center')
        self.root.attributes("-topmost", True)
        self._create_ui()
        self.root.mainloop()
        return self.result, self.student_id, self.allow_debt

    def _create_ui(self):
        main_frame = ttk.Frame(self.root, padding="20")
        main_frame.pack(fill=tk.BOTH, expand=True)

        ttk.Label(main_frame, text="🖨️ ระบบควบคุมการพิมพ์เอกสาร",
                  font=('Segoe UI', 16, 'bold'), foreground='#2c3e50').pack(pady=(0, 15))

        # ข้อมูลเอกสาร
        info_frame = ttk.LabelFrame(main_frame, text="📄 ข้อมูลเอกสาร", padding="12")
        info_frame.pack(fill=tk.X, pady=(0, 10))

        ttk.Label(info_frame, text=f"ชื่อเอกสาร: {self.print_job.document_name}",
                  font=('Segoe UI', 10)).pack(anchor=tk.W, pady=2)

        if self.print_job.pages:
            points_needed = self.print_job.pages * Config.POINTS_PER_PAGE
            ttk.Label(info_frame, text=f"📃 จำนวนหน้า: {self.print_job.pages} หน้า",
                      font=('Segoe UI', 10)).pack(anchor=tk.W, pady=2)
            ttk.Label(info_frame, text=f"💎 พอยต์ที่ต้องใช้: {points_needed} คะแนน",
                      font=('Segoe UI', 10, 'bold'), foreground='#e74c3c').pack(anchor=tk.W, pady=2)
        else:
            ttk.Label(info_frame, text="⚠️ ไม่สามารถระบุจำนวนหน้าได้",
                      font=('Segoe UI', 10), foreground='#f39c12').pack(anchor=tk.W, pady=2)

        ttk.Label(info_frame, text=f"🖨️ เครื่องพิมพ์: {self.printer_name}",
                  font=('Segoe UI', 10)).pack(anchor=tk.W, pady=2)

        # กรอกรหัสนักศึกษา
        input_frame = ttk.LabelFrame(main_frame, text="👤 ข้อมูลผู้ใช้", padding="12")
        input_frame.pack(fill=tk.X, pady=(0, 10))

        ttk.Label(input_frame, text="รหัสนักศึกษา:", font=('Segoe UI', 10)).pack(anchor=tk.W)
        self.student_entry = ttk.Entry(input_frame, font=('Segoe UI', 14), width=20)
        self.student_entry.pack(fill=tk.X, pady=(5, 0))
        self.student_entry.focus()

        # แสดง debt info ถ้า debt_limit > 0
        if Config.ALLOW_DEBT and Config.DEBT_LIMIT > 0:
            debt_info = ttk.Label(main_frame,
                text=f"⚠️ หากพอยต์ไม่พอ สามารถติดหนี้ได้สูงสุด {Config.DEBT_LIMIT} พอยต์",
                font=('Segoe UI', 9), foreground='#e67e22')
            debt_info.pack(pady=(0, 5))

        # Status label
        self.status_label = ttk.Label(main_frame, text="", font=('Segoe UI', 10), foreground='#e74c3c')
        self.status_label.pack(pady=(0, 8))

        # ปุ่ม
        button_frame = ttk.Frame(main_frame)
        button_frame.pack(fill=tk.X)

        tk.Button(button_frame, text="❌ ยกเลิก", command=self._on_cancel,
                  font=('Segoe UI', 11), bg='#95a5a6', fg='white',
                  padx=20, pady=8, cursor='hand2').pack(side=tk.RIGHT, padx=(10, 0))

        tk.Button(button_frame, text="✅ ยืนยันการพิมพ์", command=self._on_confirm,
                  font=('Segoe UI', 11, 'bold'), bg='#27ae60', fg='white',
                  padx=20, pady=8, cursor='hand2').pack(side=tk.RIGHT)

        self.root.bind('<Return>', lambda e: self._on_confirm())
        self.root.bind('<Escape>', lambda e: self._on_cancel())

    def _on_confirm(self):
        student_id = self.student_entry.get().strip()
        if not student_id:
            self.status_label.config(text="⚠️ กรุณาระบุรหัสนักศึกษา")
            return

        if self.print_job.pages:
            self.status_label.config(text="⏳ กำลังตรวจสอบพอยต์...")
            self.root.update()

            result = PrintAPI.check_student(student_id, self.print_job.pages)

            if not result.get('success'):
                # ---- พอยต์ไม่พอ: ตรวจว่าติดหนี้ได้ไหม ----
                if result.get('can_use_debt') and Config.ALLOW_DEBT:
                    shortage = result.get('points_shortage', 0)
                    balance  = result.get('points_balance', 0)
                    answer   = messagebox.askyesno(
                        "⚠️ พอยต์ไม่เพียงพอ — ต้องการติดหนี้?",
                        f"นักศึกษา: {result.get('student_name','')}\n"
                        f"พอยต์คงเหลือ: {balance}\n"
                        f"พอยต์ที่ต้องใช้: {result.get('points_needed', 0)}\n"
                        f"ขาดอีก: {shortage} พอยต์\n\n"
                        f"ยืนยันให้พิมพ์และ ติดหนี้ {shortage} พอยต์ ใช่หรือไม่?\n"
                        f"(Admin จะเรียกเก็บเงินภายหลัง)"
                    )
                    if answer:
                        self.allow_debt   = True
                        self.student_id   = student_id
                        self.result       = True
                        self.root.destroy()
                        return
                    else:
                        self.status_label.config(text="❌ ยกเลิกการติดหนี้")
                        return
                else:
                    # ไม่อนุญาตติดหนี้
                    message = result.get('message', 'ตรวจสอบไม่สำเร็จ')
                    self.status_label.config(text=f"❌ {message}")
                    if 'points_balance' in result:
                        detail = (
                            f"พอยต์คงเหลือ: {result['points_balance']}\n"
                            f"พอยต์ที่ต้องใช้: {result['points_needed']}\n"
                            f"ขาดอีก: {result['points_shortage']} พอยต์"
                        )
                        messagebox.showwarning("พอยต์ไม่เพียงพอ", detail)
                    return

        self.student_id = student_id
        self.result     = True
        self.root.destroy()

    def _on_cancel(self):
        self.result = False
        self.root.destroy()

# ===================================================
# Printer Controller
# ===================================================
class PrinterController:
    def __init__(self):
        self.printer_name   = self._get_printer_name()
        self.printer_handle = None
        self.processed_jobs = set()
        self.running        = False
        if not self.printer_name:
            raise Exception("❌ ไม่พบเครื่องปริ้นในระบบ")
        logging.info(f"✅ เตรียมพร้อมควบคุมเครื่องปริ้น: {self.printer_name}")

    def _get_printer_name(self):
        if Config.DEFAULT_PRINTER:
            return Config.DEFAULT_PRINTER
        try:
            return win32print.GetDefaultPrinter()
        except Exception as e:
            logging.error(f"Error getting default printer: {e}")
            return None

    def open_printer(self):
        try:
            self.printer_handle = win32print.OpenPrinter(self.printer_name)
            logging.info(f"✅ เชื่อมต่อกับเครื่องปริ้น {self.printer_name} สำเร็จ")
            return True
        except Exception as e:
            logging.error(f"❌ ไม่สามารถเปิดเครื่องปริ้น: {e}")
            return False

    def close_printer(self):
        if self.printer_handle:
            try:
                win32print.ClosePrinter(self.printer_handle)
                logging.info("ปิดการเชื่อมต่อเครื่องปริ้น")
            except Exception as e:
                logging.error(f"Error closing printer: {e}")

    def get_job_pages(self, job_id):
        start_time = time.time()
        while (time.time() - start_time) < Config.WAIT_PAGE_TIMEOUT:
            try:
                jobs = win32print.EnumJobs(self.printer_handle, 0, -1, 2)
                for job in jobs:
                    if job['JobId'] == job_id:
                        pages = job.get('TotalPages', 0)
                        if pages and pages > 0:
                            return pages
                time.sleep(0.3)
            except Exception as e:
                logging.error(f"Error getting job pages: {e}")
                break
        return None

    def pause_job(self, job_id):
        try:
            win32print.SetJob(self.printer_handle, job_id, 0, None, win32print.JOB_CONTROL_PAUSE)
            logging.info(f"⏸️ หยุดงานพิมพ์ชั่วคราว: Job {job_id}")
            return True
        except Exception as e:
            logging.error(f"ไม่สามารถหยุดงานพิมพ์: {e}")
            return False

    def resume_job(self, job_id):
        try:
            win32print.SetJob(self.printer_handle, job_id, 0, None, win32print.JOB_CONTROL_RESUME)
            logging.info(f"▶️ ดำเนินการพิมพ์ต่อ: Job {job_id}")
            return True
        except Exception as e:
            logging.error(f"ไม่สามารถดำเนินการพิมพ์ต่อ: {e}")
            return False

    def cancel_job(self, job_id):
        try:
            win32print.SetJob(self.printer_handle, job_id, 0, None, win32print.JOB_CONTROL_DELETE)
            logging.info(f"❌ ยกเลิกงานพิมพ์: Job {job_id}")
            return True
        except Exception as e:
            logging.error(f"ไม่สามารถยกเลิกงานพิมพ์: {e}")
            return False

    def process_print_job(self, job_data):
        job_id        = job_data['JobId']
        document_name = job_data.get('pDocument', 'Unnamed Document')
        logging.info(f"📄 ตรวจพบงานพิมพ์ใหม่: {document_name} (Job {job_id})")

        # Refresh settings จาก API ก่อนทุก job เพื่อให้ได้ค่า allow_debt / debt_limit ล่าสุด
        PrintAPI.load_settings()

        total_pages = self.get_job_pages(job_id)
        if total_pages:
            logging.info(f"📃 จำนวนหน้า: {total_pages}")
        else:
            logging.warning("⚠️ ไม่สามารถระบุจำนวนหน้าได้")

        self.pause_job(job_id)

        print_job = PrintJob(job_id, document_name, total_pages)
        dialog    = PrintConfirmationDialog(print_job, self.printer_name)
        confirmed, student_id, allow_debt = dialog.show()

        if confirmed and student_id:
            if total_pages:
                result = PrintAPI.record_print(student_id, total_pages, job_id, allow_debt=allow_debt)
                if result.get('success'):
                    pts_remaining = result.get('points_remaining', 0)
                    is_debt       = result.get('is_debt', False)
                    logging.info(f"✅ บันทึกการพิมพ์สำเร็จ: {student_id}")
                    logging.info(f"   พอยต์ที่ใช้: {result.get('points_used')}")
                    logging.info(f"   พอยต์คงเหลือ: {pts_remaining}" + (" [ติดหนี้]" if is_debt else ""))
                    PrintAPI.update_printer_status(self.printer_name, total_pages)
                    if is_debt:
                        messagebox.showwarning(
                            "⚠️ บันทึกการติดหนี้",
                            f"พิมพ์สำเร็จ แต่พอยต์ติดลบ: {pts_remaining}\n"
                            "กรุณาแจ้ง Admin เติมพอยต์โดยเร็ว"
                        )
                else:
                    logging.warning(f"⚠️ บันทึกข้อมูลล้มเหลว: {result.get('message')}")
            self.resume_job(job_id)
            logging.info(f"🖨️ อนุมัติการพิมพ์: {document_name}")
        else:
            self.cancel_job(job_id)
            logging.info(f"🚫 ยกเลิกการพิมพ์: {document_name}")

    def monitor_queue(self):
        if not self.open_printer():
            return
        self.running = True
        logging.info("👀 เริ่มตรวจสอบคิวการพิมพ์...")
        try:
            while self.running:
                try:
                    jobs = win32print.EnumJobs(self.printer_handle, 0, -1, 2)
                    for job in jobs:
                        job_id = job['JobId']
                        if job_id in self.processed_jobs:
                            continue
                        self.process_print_job(job)
                        self.processed_jobs.add(job_id)
                    if len(self.processed_jobs) > 1000:
                        self.processed_jobs = set(list(self.processed_jobs)[-500:])
                except Exception as e:
                    logging.error(f"Error in monitor loop: {e}")
                time.sleep(Config.MONITOR_INTERVAL)
        except KeyboardInterrupt:
            logging.info("⏹️ ได้รับสัญญาณหยุดการทำงาน")
        finally:
            self.close_printer()

    def stop(self):
        self.running = False

# ===================================================
# Main
# ===================================================
def print_banner():
    banner = """
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║     🖨️  ระบบควบคุมเครื่องปริ้นอัจฉริยะ                           ║
║         Smart Printer Control System                      ║
║                                                           ║
║     Version: 2.1  (Debt System Enabled)                   ║
║     Status: Ready for Deployment                          ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
"""
    print(banner)

def main():
    print_banner()
    print(f"📅 วันที่: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"📁 Log File: {log_file}")
    print(f"🌐 API URL: {Config.API_BASE_URL}")
    print()

    try:
        # โหลด settings จาก API (points_per_page, debt_limit)
        print("🔌 โหลด settings จาก API...")
        PrintAPI.load_settings()
        print(f"💎 พอยต์/หน้า: {Config.POINTS_PER_PAGE}")
        print(f"🔴 วงเงินติดหนี้: {'ไม่อนุญาต' if Config.DEBT_LIMIT == 0 else str(Config.DEBT_LIMIT) + ' พอยต์'}")
        print()

        controller = PrinterController()
        print(f"✅ เครื่องปริ้น: {controller.printer_name}")
        print()
        print("=" * 60)
        print("🚀 ระบบพร้อมทำงาน!")
        print("=" * 60)
        print()
        print("📋 คำแนะนำ:")
        print("  • หน้าต่างนี้จะแสดงสถานะการทำงาน")
        print("  • เมื่อมีงานพิมพ์ จะมีหน้าต่างยืนยันขึ้นมา")
        print("  • กรอกรหัสนักศึกษาและกดยืนยันเพื่อพิมพ์")
        print("  • กด Ctrl+C เพื่อหยุดโปรแกรม")
        print()
        print("=" * 60)
        print()

        controller.monitor_queue()

    except KeyboardInterrupt:
        print("\n\n⏹️ หยุดโปรแกรมโดยผู้ใช้")
    except Exception as e:
        logging.error(f"❌ เกิดข้อผิดพลาด: {e}")
        print(f"\n❌ ข้อผิดพลาด: {e}")
        print("\n💡 คำแนะนำ:")
        print("  1. ตรวจสอบว่าเครื่องปริ้นเชื่อมต่ออยู่")
        print("  2. ตรวจสอบ API URL ใน Config")
        print("  3. ดู Log ที่: " + log_file)
        input("\nกด Enter เพื่อออก...")

if __name__ == "__main__":
    main()
