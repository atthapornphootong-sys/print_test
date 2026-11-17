"""
===============================================
Print Queue Manager
ระบบจัดการคิวการพิมพ์แบบ Background
===============================================
ไฟล์นี้ใช้สำหรับจัดการคิวการพิมพ์จาก Web Interface
"""

import time
import logging
import requests
import win32print
import win32api
from datetime import datetime
import os

# ตั้งค่า Logging
log_file = os.path.join(os.path.dirname(__file__), 'print_queue_manager.log')
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(log_file, encoding='utf-8'),
        logging.StreamHandler()
    ]
)

# ===================================================
# การตั้งค่า
# ===================================================
class Config:
    API_BASE_URL = "http://localhost/print_test/config/controller.php"
    CHECK_INTERVAL = 5  # ตรวจสอบทุก 5 วินาที
    PRINTER_NAME = None  # None = Default Printer

# ===================================================
# Queue Manager
# ===================================================
class PrintQueueManager:
    """จัดการคิวการพิมพ์จาก Database"""
    
    def __init__(self):
        self.running = False
        self.printer_name = self._get_printer_name()
        
    def _get_printer_name(self):
        """หาชื่อเครื่องปริ้น"""
        if Config.PRINTER_NAME:
            return Config.PRINTER_NAME
        try:
            return win32print.GetDefaultPrinter()
        except:
            return None
    
    def get_pending_jobs(self):
        """ดึงงานพิมพ์ที่รอจาก Database"""
        try:
            response = requests.post(
                Config.API_BASE_URL,
                json={'action': 'get_pending_print_jobs'},
                timeout=5
            )
            result = response.json()
            
            if result.get('success'):
                return result.get('jobs', [])
            return []
        except Exception as e:
            logging.error(f"Error getting pending jobs: {e}")
            return []
    
    def print_file(self, file_path):
        """พิมพ์ไฟล์"""
        try:
            # ใช้ ShellExecute เพื่อพิมพ์
            win32api.ShellExecute(
                0,
                "print",
                file_path,
                None,
                ".",
                0
            )
            logging.info(f"✅ พิมพ์ไฟล์สำเร็จ: {file_path}")
            return True
        except Exception as e:
            logging.error(f"❌ พิมพ์ไฟล์ล้มเหลว: {e}")
            return False
    
    def update_job_status(self, job_id, status):
        """อัพเดทสถานะงานพิมพ์"""
        try:
            response = requests.post(
                Config.API_BASE_URL,
                json={
                    'action': 'update_print_job_status',
                    'job_id': job_id,
                    'status': status
                },
                timeout=5
            )
            return response.json().get('success', False)
        except Exception as e:
            logging.error(f"Error updating job status: {e}")
            return False
    
    def process_jobs(self):
        """ประมวลผลงานพิมพ์ที่รอ"""
        jobs = self.get_pending_jobs()
        
        for job in jobs:
            job_id = job.get('job_id')
            file_path = job.get('file_path')
            student_id = job.get('student_id')
            
            logging.info(f"📄 กำลังพิมพ์: {file_path} (Student: {student_id})")
            
            # อัพเดทสถานะเป็น processing
            self.update_job_status(job_id, 'processing')
            
            # พิมพ์ไฟล์
            success = self.print_file(file_path)
            
            # อัพเดทสถานะ
            if success:
                self.update_job_status(job_id, 'completed')
                logging.info(f"✅ พิมพ์เสร็จสิ้น: Job {job_id}")
            else:
                self.update_job_status(job_id, 'failed')
                logging.error(f"❌ พิมพ์ล้มเหลว: Job {job_id}")
    
    def run(self):
        """รันระบบจัดการคิว"""
        self.running = True
        logging.info("🚀 เริ่มระบบจัดการคิวการพิมพ์")
        
        if not self.printer_name:
            logging.error("❌ ไม่พบเครื่องปริ้น")
            return
        
        logging.info(f"🖨️ เครื่องปริ้น: {self.printer_name}")
        
        try:
            while self.running:
                self.process_jobs()
                time.sleep(Config.CHECK_INTERVAL)
        except KeyboardInterrupt:
            logging.info("⏹️ หยุดการทำงานโดยผู้ใช้")
        finally:
            self.running = False

# ===================================================
# Main
# ===================================================
def main():
    print("""
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║     📋 Print Queue Manager                                ║
║        ระบบจัดการคิวการพิมพ์                                  ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
""")
    
    print(f"📅 วันที่: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"📁 Log File: {log_file}")
    print(f"🌐 API URL: {Config.API_BASE_URL}")
    print()
    print("กำลังเริ่มต้นระบบ...")
    print()
    
    manager = PrintQueueManager()
    manager.run()

if __name__ == "__main__":
    main()