<?php
require_once '../../config/auth.php'; // ตรวจสอบ Login
$page_title = "จัดการนักศึกษา";
require_once '../../static/header.php';
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header"><h3>นักศึกษาทั้งหมด</h3><div class="stat-icon blue">👥</div></div>
        <div class="stat-value" id="totalStudents">-</div><div class="stat-label">คน</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header"><h3>ใช้งานวันนี้</h3><div class="stat-icon green">✅</div></div>
        <div class="stat-value" id="activeToday">-</div><div class="stat-label">คน</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header"><h3>จำนวนคณะ</h3><div class="stat-icon orange">🏫</div></div>
        <div class="stat-value" id="totalFaculties">-</div><div class="stat-label">คณะ</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header"><h3>จำนวนสาขา</h3><div class="stat-icon purple">📚</div></div>
        <div class="stat-value" id="totalMajors">-</div><div class="stat-label">สาขา</div>
    </div>
</div>

<!-- Tabs -->
<div class="card" style="padding:0;overflow:hidden;">
    <div style="display:flex;border-bottom:2px solid #eee;background:#f8f9fa;">
        <button class="tab-btn active" onclick="switchTab('students')" id="tab-students">👥 นักศึกษา</button>
        <button class="tab-btn" onclick="switchTab('majors')"    id="tab-majors">📚 สาขา</button>
        <button class="tab-btn" onclick="switchTab('faculties')" id="tab-faculties">🏫 คณะ</button>
    </div>

    <!-- Tab: Students -->
    <div id="panel-students" class="tab-panel">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eee;">
            <h2 style="margin:0;">รายชื่อนักศึกษา</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <div style="position:relative;">
                    <input type="text" id="filterMajorInput" placeholder="📚 ทุกสาขา" class="form-control" style="width:220px;" 
                        oninput="onMajorSearch()" onfocus="showMajorDropdown()" onblur="setTimeout(()=>hideMajorDropdown(),300)" autocomplete="off">
                    <div id="majorDropdown" style="display:none;position:absolute;top:100%;left:0;right:0;background:white;border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.1);z-index:999;max-height:240px;overflow-y:auto;min-width:260px;"></div>
                    <input type="hidden" id="filterMajor">
                    <input type="hidden" id="filterFaculty">
                </div>
                <div style="position:relative;">
                    <input type="text" id="searchInput" placeholder="🔍 ค้นหารหัส/ชื่อ..." class="form-control" style="width:220px;" 
                        oninput="onSearch()" onfocus="showHistory()" onblur="setTimeout(hideHistory,200)" autocomplete="off">
                    <div id="searchHistory" style="display:none;position:absolute;top:100%;left:0;right:0;background:white;border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:999;min-width:280px;"></div>
                </div>
                <button id="btnDebtFilter" onclick="toggleDebtFilter()" class="btn btn-sm" style="background:#ef4444;color:white;display:none;">🔴 ติดหนี้ <span id="debtFilterCount"></span></button>
                <button onclick="showAddStudentModal()" class="btn btn-success btn-sm">➕ เพิ่มนักศึกษา</button>
                <button onclick="showImportModal()" class="btn btn-sm" style="background:#fd7e14;color:white;">📥 Import Excel</button>
            </div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>รหัสนักศึกษา</th><th>ชื่อ-สกุล</th><th>สาขา</th><th>คณะ</th>
                        <th>พอยต์</th><th>สถานะ</th><th style="width:130px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="studentsBody">
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Majors -->
    <div id="panel-majors" class="tab-panel" style="display:none;">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eee;">
            <h2 style="margin:0;">จัดการสาขา</h2>
            <button onclick="showAddMajorModal()" class="btn btn-success btn-sm">➕ เพิ่มสาขา</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>รหัสสาขา</th><th>ชื่อสาขา</th><th>คณะ</th><th>นักศึกษา</th><th style="width:140px;">จัดการ</th></tr>
                </thead>
                <tbody id="majorsBody">
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:#999;">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Faculties -->
    <div id="panel-faculties" class="tab-panel" style="display:none;">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eee;">
            <h2 style="margin:0;">จัดการคณะ</h2>
            <button onclick="showAddFacultyModal()" class="btn btn-success btn-sm">➕ เพิ่มคณะ</button>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>รหัสคณะ</th><th>ชื่อคณะ</th><th>จำนวนสาขา</th><th style="width:140px;">จัดการ</th></tr>
                </thead>
                <tbody id="facultiesBody">
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:#999;">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ══════════════════ MODALS ══════════════════ -->

<!-- Modal: เพิ่ม/แก้ไขนักศึกษา -->
<div id="studentModal" class="modal">
    <div class="modal-content" style="max-width:520px;">
        <span class="close-modal" onclick="closeModal('studentModal')">&times;</span>
        <h2 style="margin-bottom:20px;" id="studentModalTitle">➕ เพิ่มนักศึกษาใหม่</h2>
        <input type="hidden" id="stu_mode">
        <div class="form-group">
            <label>รหัสนักศึกษา <span style="color:red;">*</span></label>
            <input type="text" id="stu_id" class="form-control" placeholder="เช่น 6501234">
        </div>
        <div class="form-group">
            <label>ชื่อ-สกุล <span style="color:red;">*</span></label>
            <input type="text" id="stu_name" class="form-control" placeholder="ชื่อ นามสกุล">
        </div>
        <div class="form-group">
            <label>สาขา <span style="color:#999;font-size:12px;">(ระบบจะดึงคณะให้อัตโนมัติ)</span></label>
            <select id="stu_major" class="form-control">
                <option value="">— เลือกสาขา —</option>
            </select>
            <div id="stu_faculty_display" style="margin-top:6px;font-size:13px;color:#003087;display:none;">
                🏫 คณะ: <span id="stu_faculty_name"></span>
            </div>
        </div>
        <div class="form-group" id="stu_points_group">
            <label>พอยต์เริ่มต้น</label>
            <input type="number" id="stu_points" class="form-control" placeholder="0" value="0" min="0">
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button onclick="closeModal('studentModal')" class="btn" style="flex:1;background:#ddd;color:#333;">ยกเลิก</button>
            <button onclick="saveStudent()" class="btn btn-primary" style="flex:1;" id="stuSaveBtn">💾 บันทึก</button>
        </div>
    </div>
</div>

<!-- Modal: เพิ่มนักศึกษาหลายคน -->
<div id="multiAddModal" class="modal">
    <div class="modal-content" style="max-width:820px;">
        <span class="close-modal" onclick="closeModal('multiAddModal')">&times;</span>
        <h2 style="margin-bottom:6px;">➕ เพิ่มนักศึกษา</h2>
        <div style="font-size:13px;color:#666;margin-bottom:14px;">กรอกได้หลายคนพร้อมกัน — กด "เพิ่มแถว" หรือกด Enter ที่แถวสุดท้ายเพื่อเพิ่มแถวใหม่ (แถวว่างจะถูกข้าม)</div>
        <div style="display:grid;grid-template-columns:120px 1fr 1fr 80px 36px;gap:8px;font-size:13px;font-weight:bold;color:#555;padding:0 2px;margin-bottom:6px;">
            <div>รหัส <span style="color:red;">*</span></div>
            <div>ชื่อ-สกุล <span style="color:red;">*</span></div>
            <div>สาขา</div>
            <div>พอยต์</div>
            <div></div>
        </div>
        <div id="multiAddRows" style="max-height:46vh;overflow-y:auto;display:flex;flex-direction:column;gap:8px;padding:2px;"></div>
        <button onclick="addStudentRow()" class="btn btn-sm" style="margin-top:10px;background:#e8ecff;color:#003087;">➕ เพิ่มแถว</button>
        <div id="multiAddResult" style="display:none;border-radius:8px;padding:12px;margin-top:12px;font-size:14px;"></div>
        <div style="display:flex;gap:10px;margin-top:18px;">
            <button onclick="closeModal('multiAddModal')" class="btn" style="flex:1;background:#ddd;color:#333;">ปิด</button>
            <button onclick="saveMultiStudents()" id="multiSaveBtn" class="btn btn-success" style="flex:1;">💾 บันทึกทั้งหมด</button>
        </div>
    </div>
</div>

<!-- Modal: ลบนักศึกษา -->
<div id="deleteStudentModal" class="modal">
    <div class="modal-content" style="max-width:440px;text-align:center;">
        <div style="font-size:60px;margin-bottom:10px;">🗑️</div>
        <h2 style="color:#b43030;margin-bottom:10px;">ยืนยันการลบ</h2>
        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:15px;margin:15px 0;text-align:left;">
            <div style="font-weight:bold;margin-bottom:8px;color:#856404;">⚠️ ข้อมูลที่จะถูกลบ:</div>
            <table style="width:100%;font-size:14px;">
                <tr><td style="color:#666;padding:3px 0;">👤 ชื่อ</td><td id="del_stu_name" style="font-weight:bold;">-</td></tr>
                <tr><td style="color:#666;padding:3px 0;">🆔 รหัส</td><td id="del_stu_id"   style="font-weight:bold;">-</td></tr>
                <tr><td style="color:#666;padding:3px 0;">💰 พอยต์</td><td id="del_stu_pts" style="font-weight:bold;">-</td></tr>
            </table>
        </div>
        <div id="del_stu_warn" style="display:none;background:#f8d7da;border:1px solid #b43030;border-radius:8px;padding:12px;margin-bottom:12px;color:#721c24;text-align:left;font-size:14px;"></div>
        <div style="display:flex;gap:10px;">
            <button onclick="closeModal('deleteStudentModal')" class="btn" style="flex:1;background:#ddd;color:#333;">ยกเลิก</button>
            <button onclick="confirmDeleteStudent()" id="delStuBtn" class="btn" style="flex:1;background:#b43030;color:white;">🗑️ ลบถาวร</button>
        </div>
    </div>
</div>

<!-- Modal: คณะ -->
<div id="facultyModal" class="modal">
    <div class="modal-content" style="max-width:460px;">
        <span class="close-modal" onclick="closeModal('facultyModal')">&times;</span>
        <h2 style="margin-bottom:20px;" id="facultyModalTitle">➕ เพิ่มคณะ</h2>
        <input type="hidden" id="fac_id">
        <div class="form-group">
            <label>รหัสคณะ (2 หลัก เช่น 01, 02) <span style="color:red;">*</span></label>
            <input type="text" id="fac_code" class="form-control" placeholder="01" maxlength="2">
        </div>
        <div class="form-group">
            <label>ชื่อคณะ <span style="color:red;">*</span></label>
            <input type="text" id="fac_name" class="form-control" placeholder="คณะวิศวกรรมศาสตร์">
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button onclick="closeModal('facultyModal')" class="btn" style="flex:1;background:#ddd;color:#333;">ยกเลิก</button>
            <button onclick="saveFaculty()" class="btn btn-primary" style="flex:1;">💾 บันทึก</button>
        </div>
    </div>
</div>

<!-- Modal: สาขา -->
<div id="majorModal" class="modal">
    <div class="modal-content" style="max-width:460px;">
        <span class="close-modal" onclick="closeModal('majorModal')">&times;</span>
        <h2 style="margin-bottom:20px;" id="majorModalTitle">➕ เพิ่มสาขา</h2>
        <input type="hidden" id="maj_id">
        <div class="form-group">
            <label>คณะที่สังกัด <span style="color:red;">*</span></label>
            <select id="maj_faculty" class="form-control"></select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label>รหัสสาขา (2 หลัก เช่น 01) <span style="color:red;">*</span></label>
                <input type="text" id="maj_id_input" class="form-control" placeholder="01" maxlength="2">
            </div>
            <div class="form-group">
                <label>ตัวย่อสาขา (เช่น ME, IT) <span style="color:red;">*</span></label>
                <input type="text" id="maj_abbr" class="form-control" placeholder="ME" style="text-transform:uppercase;" maxlength="5">
            </div>
        </div>
        <div class="form-group">
            <label>ชื่อสาขา <span style="color:red;">*</span></label>
            <input type="text" id="maj_name" class="form-control" placeholder="วิศวกรรมเครื่องกล">
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button onclick="closeModal('majorModal')" class="btn" style="flex:1;background:#ddd;color:#333;">ยกเลิก</button>
            <button onclick="saveMajor()" class="btn btn-primary" style="flex:1;">💾 บันทึก</button>
        </div>
    </div>
</div>

<!-- Modal: Import Excel -->
<div id="importModal" class="modal">
    <div class="modal-content" style="max-width:620px;">
        <span class="close-modal" onclick="closeModal('importModal')">&times;</span>
        <h2 style="margin-bottom:10px;">📥 Import นักศึกษาจาก Excel</h2>

        <!-- คำแนะนำ -->
        <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#1b5e20;">
            ✅ รองรับชื่อคอลัมน์ <strong>ภาษาไทยและอังกฤษ</strong><br>
            &bull; Excel: คอลัมน์ <code>รหัสนักศึกษา</code>/<code>รหัสประจำตัว</code> และ <code>ชื่อ</code>/<code>ชื่อ-สกุล</code><br>
            &bull; ไฟล์ .asp (RTF) จากระบบมหาวิทยาลัย: ลากวางได้เลย ไม่ต้องแปลง
        </div>

        <!-- Drop Zone -->
        <div id="importDropzone"
             onclick="document.getElementById('excelFile').click()"
             ondragover="event.preventDefault();this.style.background='#e8ecff';this.style.borderColor='#4a5de8';"
             ondragleave="this.style.background='#f8f9ff';this.style.borderColor='#003087';"
             ondrop="handleDrop(event)"
             style="border:3px dashed #003087;border-radius:12px;padding:36px;text-align:center;cursor:pointer;background:#f8f9ff;transition:all .2s;margin-bottom:14px;">
            <div style="font-size:44px;margin-bottom:8px;">📂</div>
            <div id="dropzoneText" style="font-size:16px;color:#003087;font-weight:bold;">คลิกหรือลากไฟล์ Excel มาวาง</div>
            <div style="font-size:13px;color:#999;margin-top:4px;">รองรับทุกไฟล์ Excel</div>
        </div>
        <input type="file" id="excelFile" accept="*" style="display:none;" onchange="handleFileSelect(this)">

        <!-- คอลัมน์ที่พบ -->
        <div id="importColInfo" style="display:none;background:#e3f2fd;border:1px solid #90caf9;border-radius:6px;padding:8px 14px;margin-bottom:10px;font-size:13px;color:#0d47a1;"></div>

        <!-- Preview -->
        <div id="importPreview" style="display:none;">

            <!-- เลือกสาขา -->
            <div style="background:#fff8e1;border:1px solid #ffc107;border-radius:8px;padding:12px;margin-bottom:12px;">
                <strong style="color:#856404;font-size:14px;">📌 กำหนดสาขาให้ทุกแถว (ไม่บังคับ — เลือกรายคนได้ในตาราง)</strong>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
                    <select id="importFaculty" class="form-control" onchange="onImportFacultyChange()">
                        <option value="">— ไม่ระบุคณะ —</option>
                    </select>
                    <select id="importMajor" class="form-control" onchange="onImportMajorChange()">
                        <option value="">— ไม่ระบุสาขา —</option>
                    </select>
                </div>
            </div>

            <!-- ตารางตัวอย่าง -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <strong style="font-size:14px;">ข้อมูลที่จะนำเข้า (เลือกสาขารายคนได้)</strong>
                <span id="importRowCount" style="color:#003087;font-weight:bold;font-size:14px;"></span>
            </div>
            <div style="border:1px solid #eee;border-radius:8px;overflow:hidden;max-height:300px;overflow-y:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead style="background:#f8f9fa;position:sticky;top:0;">
                        <tr>
                            <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #ddd;">#</th>
                            <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #ddd;">รหัสนักศึกษา</th>
                            <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #ddd;">ชื่อ-สกุล</th>
                            <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #ddd;width:180px;">สาขา</th>
                        </tr>
                    </thead>
                    <tbody id="importPreviewBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ผลลัพธ์ -->
        <div id="importResult" style="display:none;border-radius:8px;padding:14px;margin-top:12px;font-size:14px;"></div>

        <div style="display:flex;gap:10px;margin-top:18px;">
            <button onclick="closeModal('importModal')" class="btn" style="flex:1;background:#ddd;color:#333;">ปิด</button>
            <button onclick="doImport()" id="importBtn" style="flex:1;display:none;background:#14764e;color:white;border:none;border-radius:8px;padding:12px;font-size:15px;cursor:pointer;font-weight:bold;">
                📥 นำเข้าข้อมูล &nbsp;<span id="importCount" style="background:rgba(255,255,255,.3);padding:2px 8px;border-radius:20px;">0</span> คน
            </button>
        </div>
    </div>
</div>

<!-- Modal: สำเร็จ -->
<div id="successModal" class="modal">
    <div class="modal-content" style="text-align:center;max-width:350px;">
        <div style="font-size:60px;margin-bottom:10px;">✅</div>
        <h2 style="color:#14764e;margin-bottom:10px;">สำเร็จ!</h2>
        <p id="successMsg" style="margin-bottom:20px;">ดำเนินการเรียบร้อย</p>
        <button onclick="closeModal('successModal')" class="btn btn-primary" style="width:100%;">ตกลง</button>
    </div>
</div>

<!-- ══════════════════ STYLES ══════════════════ -->
<style>
.tab-btn { padding:14px 28px;border:none;background:transparent;font-size:15px;cursor:pointer;color:#666;border-bottom:3px solid transparent;transition:all .2s;font-weight:500; }
.tab-btn:hover  { color:#003087;background:#f0f2ff; }
.tab-btn.active { color:#003087;border-bottom-color:#003087;font-weight:bold;background:white; }
.badge { padding:4px 10px;border-radius:50px;font-size:12px;font-weight:bold;color:white;display:inline-block; }
.badge-success   { background:#14764e; }
.badge-warning   { background:#ffc107;color:#333; }
.badge-danger    { background:#b43030; }
.badge-secondary { background:#6c757d; }
.badge-info      { background:#17a2b8; }
.stat-icon.purple { background:rgba(102,78,234,.1);color:#003087; }
.modal { display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);align-items:center;justify-content:center; }
.modal.show { display:flex !important; }
.modal-content { background:white;padding:30px;border-radius:15px;width:90%;max-height:90vh;overflow-y:auto;position:relative; }
.close-modal { position:absolute;top:10px;right:15px;font-size:28px;cursor:pointer;color:#aaa;line-height:1; }
.close-modal:hover { color:#333; }
code { background:#f0f0f0;padding:1px 5px;border-radius:3px;font-size:12px; }

/* ── Tab panel slide in ── */
.tab-panel { animation: gFadeUp .35s ease both; }

/* ── Tab button hover bounce ── */
.tab-btn { transition: all .2s cubic-bezier(.34,1.56,.64,1) !important; }
.tab-btn:hover { transform: translateY(-2px); }

/* ── Import dropzone pulse border ── */
#importDropzone {
  transition: all .25s ease;
}
#importDropzone:hover {
  border-color: #4a5de8 !important;
  background: #eef0ff !important;
  transform: scale(1.01);
  box-shadow: 0 4px 16px rgba(74,93,232,.15);
}

/* ── Success modal icon bounce ── */
#successModal .modal-content > div:first-child {
  animation: gBounce .6s .15s cubic-bezier(.34,1.56,.64,1) both;
  display: block;
}

/* ── Preview rows fade ── */
#importPreviewBody tr {
  animation: gFadeUp .3s ease both;
}
#importPreviewBody tr:nth-child(1){animation-delay:.05s}
#importPreviewBody tr:nth-child(2){animation-delay:.10s}
#importPreviewBody tr:nth-child(3){animation-delay:.15s}
#importPreviewBody tr:nth-child(4){animation-delay:.20s}
#importPreviewBody tr:nth-child(5){animation-delay:.25s}
</style>

<!-- SheetJS สำหรับอ่าน Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- ══════════════════ SCRIPT ══════════════════ -->
<script>
let allStudents  = [];
let allFaculties = [];
let allMajors    = [];
let currentDeleteStudentId = '';
let debtFilterActive = false;
const API = '../../config/controller.php';

// ─── Tab ─────────────────────────────────────────────
function switchTab(tab) {
    ['students','majors','faculties'].forEach(t => {
        document.getElementById('panel-'+t).style.display = (t===tab) ? '' : 'none';
        document.getElementById('tab-'+t).classList.toggle('active', t===tab);
    });
    if (tab==='students')  loadStudents();
    if (tab==='majors')    loadMajors();
    if (tab==='faculties') loadFaculties();
}

// ════ SEARCH HISTORY (localStorage, หมดอายุ 7 วัน) ════════
const STORAGE_KEY  = 'ps_searchHistory';   // รายการประวัติ
const LAST_KEY     = 'ps_lastSearch';      // คำค้นล่าสุด
const EXPIRE_DAYS  = 7;                    // หมดอายุกี่วัน
const MAX_HISTORY  = 10;                   // เก็บสูงสุดกี่รายการ

function getHistory() {
    try {
        const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        const now = Date.now();
        // กรองรายการที่หมดอายุออก
        return raw.filter(x => (now - (x.ts || 0)) < EXPIRE_DAYS * 86400000);
    } catch(e) { return []; }
}

function saveHistory(q) {
    if (!q || q.length < 2) return;
    const now  = Date.now();
    let h = getHistory().filter(x => x.val !== q);
    h.unshift({ val: q, ts: now });
    if (h.length > MAX_HISTORY) h = h.slice(0, MAX_HISTORY);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(h));
    localStorage.setItem(LAST_KEY, JSON.stringify({ val: q, ts: now }));
}

function getLastSearch() {
    try {
        const raw = JSON.parse(localStorage.getItem(LAST_KEY) || 'null');
        if (!raw) return '';
        const expired = (Date.now() - (raw.ts || 0)) > EXPIRE_DAYS * 86400000;
        return expired ? '' : raw.val;
    } catch(e) { return ''; }
}

function clearHistory() {
    localStorage.removeItem(STORAGE_KEY);
    localStorage.removeItem(LAST_KEY);
}

function showHistory() {
    const h   = getHistory();
    const box = document.getElementById('searchHistory');
    const q   = document.getElementById('searchInput').value.trim();
    if (!h.length) { box.style.display='none'; return; }
    const filtered = h.filter(x => !q || x.val.includes(q));
    if (!filtered.length) { box.style.display='none'; return; }
    const now = Date.now();
    box.innerHTML = `
        <div style="padding:6px 12px;font-size:11px;color:#999;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
            <span>🕐 ประวัติการค้นหา (เก็บ ${EXPIRE_DAYS} วัน)</span>
            <span onclick="clearHistory();hideHistory();loadStudents();" style="cursor:pointer;color:#b43030;font-size:12px;">ล้างทั้งหมด</span>
        </div>
        ${filtered.map(x => {
            const days = Math.floor((now - x.ts) / 86400000);
            const ago  = days === 0 ? 'วันนี้' : `${days} วันที่แล้ว`;
            return `<div onclick="selectHistory('${x.val}')"
                 style="padding:9px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid #f8f8f8;display:flex;justify-content:space-between;align-items:center;"
                 onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background=''">
                <span><span style="color:#999;">🔍</span> ${x.val}</span>
                <span style="font-size:11px;color:#bbb;">${ago}</span>
            </div>`;
        }).join('')}
    `;
    box.style.display = '';
}
function hideHistory() { document.getElementById('searchHistory').style.display='none'; }
function selectHistory(q) {
    document.getElementById('searchInput').value = q;
    hideHistory();
    loadStudents();
}
function onSearch() {
    const q = document.getElementById('searchInput').value.trim();
    showHistory();
    loadStudents();
    if (q) saveHistory(q);
}

// ─── Students ────────────────────────────────────────
async function loadStudents() {
    const search    = document.getElementById('searchInput').value.trim();
    const majorId   = document.getElementById('filterMajor').value;
    const facultyId = document.getElementById('filterFaculty').value;
    const tbody     = document.getElementById('studentsBody');

    // บันทึกการค้นหาล่าสุด
    if (search) saveHistory(search);

    try {
        const url = search
            ? `${API}?action=search_students&q=${encodeURIComponent(search)}`
            : `${API}?action=get_all_students`;
        const res = await fetch(url).then(r => r.json());
        if (!res.success || !res.data || !res.data.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">ไม่พบข้อมูล</td></tr>';
            updateStudentStats([]);
            return;
        }
        let data = res.data;
        // กรองตามคณะ (เฉพาะเมื่อ allMajors โหลดแล้ว)
        if (facultyId && allMajors.length) {
            data = data.filter(s => {
                const m = allMajors.find(m => String(m.major_id) === String(s.major_id));
                return m && String(m.faculty_id) === String(facultyId);
            });
        }
        // กรองตามสาขา
        if (majorId) data = data.filter(s => String(s.major_id) === String(majorId));

        // อัพเดตปุ่มติดหนี้
        const debtAll = res.data.filter(s => parseInt(s.points_balance) < 0);
        const btnDebt = document.getElementById('btnDebtFilter');
        if (debtAll.length > 0) {
            btnDebt.style.display = '';
            document.getElementById('debtFilterCount').textContent = `(${debtAll.length} คน)`;
        } else {
            btnDebt.style.display = 'none';
        }
        // กรองเฉพาะติดหนี้ถ้าเปิด filter
        if (debtFilterActive) data = data.filter(s => parseInt(s.points_balance) < 0);

        allStudents = data;
        updateStudentStats(data);
        tbody.innerHTML = data.map(s => {
            let badge, badgeText;
            const pts = parseInt(s.points_balance) || 0;
            if      (pts < 0)  { badge='badge-danger';   badgeText='🔴 ติดหนี้'; }
            else if (pts <= 10) { badge='badge-warning';  badgeText='⚠️ พอยต์ต่ำ'; }
            else                { badge='badge-success';  badgeText='ปกติ'; }
            const mText = s.major_name
                ? `<span class="badge badge-info" style="font-size:11px;">${s.major_abbr||s.major_id}</span> ${s.major_name}`
                : '<span style="color:#ccc;">-</span>';
            const fText = s.faculty_name
                ? s.faculty_name
                : '<span style="color:#ccc;">-</span>';
            return `<tr>
                <td><strong>${s.student_id}</strong></td>
                <td>${s.student_name}</td>
                <td style="font-size:13px;">${mText}</td>
                <td style="font-size:13px;">${fText}</td>
                <td style="font-weight:bold;color:${parseInt(s.points_balance)<0?'#c53030':'#003087'};">${s.points_balance}${parseInt(s.points_balance)<0?' <small style="color:#c53030;">(ติดหนี้)</small>':''}</td>
                <td><span class="badge ${badge}">${badgeText}</span></td>
                <td>
                    <button onclick="showEditStudentModal('${s.student_id}')" class="btn btn-primary btn-sm" style="padding:5px 9px;font-size:12px;">✏️</button>
                    <button onclick="showDeleteStudentModal('${s.student_id}')" class="btn btn-danger btn-sm" style="padding:5px 9px;font-size:12px;">🗑️</button>
                </td>
            </tr>`;
        }).join('');
        if (window.reAnimateRows) reAnimateRows('studentsBody');
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:red;">❌ โหลดข้อมูลไม่สำเร็จ</td></tr>';
    }
}

function updateStudentStats(data) {
    const today = new Date().toDateString();
    document.getElementById('totalStudents').textContent = data.length;
    document.getElementById('activeToday').textContent   = data.filter(s => s.last_active && new Date(s.last_active).toDateString()===today).length;
}

// ─── Searchable Major Filter (เดียว — ไม่ต้องเลือกคณะแยก) ──────────────
function onMajorSearch() {
    const q = document.getElementById('filterMajorInput').value.toLowerCase();
    if (!q) {
        document.getElementById('filterMajor').value  = '';
        document.getElementById('filterFaculty').value = '';
        loadStudents();
    }
    showMajorDropdown();
}
function showMajorDropdown() {
    const q   = document.getElementById('filterMajorInput').value.toLowerCase();
    const box = document.getElementById('majorDropdown');

    // จัดกลุ่มตามคณะ
    const groups = {};
    allMajors.forEach(m => {
        const fid = m.faculty_id;
        if (!groups[fid]) groups[fid] = { name: m.faculty_name, majors: [] };
        groups[fid].majors.push(m);
    });

    // กรองตามที่พิมพ์
    let html = '';
    // ตัวเลือกแรก: ทุกสาขา
    if (!q) {
        html += `<div onclick="selectMajorFilter('','','ทุกสาขา')"
            style="padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:2px solid #eee;color:#666;font-weight:bold;"
            onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background=''">— ทุกสาขา —</div>`;
    }

    Object.values(groups).forEach(g => {
        const filtered = g.majors.filter(m =>
            !q ||
            m.major_name.toLowerCase().includes(q) ||
            (m.major_abbr || '').toLowerCase().includes(q) ||
            String(m.major_id).includes(q)
        );
        if (!filtered.length) return;

        // หัว group คณะ
        html += `<div style="padding:5px 12px;font-size:11px;color:#999;background:#f8f9fa;border-bottom:1px solid #eee;font-weight:bold;">${g.name}</div>`;
        filtered.forEach(m => {
            html += `<div onclick="selectMajorFilter('${m.major_id}','${m.faculty_id}','${m.major_name}')"
                style="padding:8px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid #f5f5f5;display:flex;justify-content:space-between;align-items:center;"
                onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background=''">
                <span>${m.major_abbr ? '<span style="background:#e3f2fd;color:#1565c0;padding:1px 6px;border-radius:10px;font-size:11px;margin-right:6px;">'+m.major_abbr+'</span>' : ''}${m.major_name}</span>
            </div>`;
        });
    });

    if (!html) {
        html = `<div style="padding:12px;text-align:center;color:#999;font-size:13px;">ไม่พบสาขา</div>`;
    }

    box.innerHTML = html;
    box.style.display = '';
}
function hideMajorDropdown() { document.getElementById('majorDropdown').style.display = 'none'; }
function selectMajorFilter(majorId, facultyId, majorName) {
    document.getElementById('filterMajor').value       = majorId;
    document.getElementById('filterFaculty').value     = facultyId;
    document.getElementById('filterMajorInput').value  = majorId ? majorName : '';
    hideMajorDropdown();
    loadStudents();
}

// ─── ฟังก์ชันเดิมที่ยังต้องมีไว้ (ใช้ใน refreshMajorDropdownItems) ────────
function onFacultySearch() {}
function showFacultyDropdown() {}
function hideFacultyDropdown() {}
function selectFaculty() {}
function refreshMajorDropdownItems() {
    document.getElementById('filterMajor').value      = '';
    document.getElementById('filterMajorInput').value = '';
    document.getElementById('filterFaculty').value    = '';
}
function filterByFaculty() { loadStudents(); }

// ─── เพิ่มนักศึกษาหลายคนพร้อมกัน ─────────────────────
function majorOptionsHtml(placeholder = '— เลือกสาขา —') {
    let html = `<option value="">${placeholder}</option>`;
    const groups = {};
    allMajors.forEach(m => {
        if (!groups[m.faculty_id]) groups[m.faculty_id] = { name: m.faculty_name, majors: [] };
        groups[m.faculty_id].majors.push(m);
    });
    Object.values(groups).forEach(g => {
        html += `<optgroup label="${g.name}">`;
        g.majors.forEach(m => html += `<option value="${m.major_id}">${m.major_abbr ? '['+m.major_abbr+'] ' : ''}${m.major_name}</option>`);
        html += '</optgroup>';
    });
    return html;
}

async function showAddStudentModal() {
    if (!allFaculties.length) await loadFaculties();
    if (!allMajors.length)    await loadMajors();
    document.getElementById('multiAddRows').innerHTML = '';
    document.getElementById('multiAddResult').style.display = 'none';
    addStudentRow(false); addStudentRow(false); addStudentRow(false);
    document.getElementById('multiAddModal').classList.add('show');
}

function addStudentRow(focus = true) {
    const wrap = document.getElementById('multiAddRows');
    const row  = document.createElement('div');
    row.className = 'multi-row';
    row.style.cssText = 'display:grid;grid-template-columns:120px 1fr 1fr 80px 36px;gap:8px;align-items:center;';
    row.innerHTML = `
        <input type="text" class="form-control m-id" placeholder="6501234" onkeydown="rowEnter(event,this)">
        <input type="text" class="form-control m-name" placeholder="ชื่อ นามสกุล" onkeydown="rowEnter(event,this)">
        <select class="form-control m-major">${majorOptionsHtml()}</select>
        <input type="number" class="form-control m-pts" value="0" min="0">
        <button onclick="this.parentElement.remove()" title="ลบแถว" style="border:none;background:#fde8e8;color:#b43030;border-radius:6px;height:34px;cursor:pointer;font-size:15px;">✕</button>`;
    wrap.appendChild(row);
    if (focus) row.querySelector('.m-id').focus();
}

function rowEnter(e, el) {
    if (e.key !== 'Enter') return;
    const rows = document.querySelectorAll('#multiAddRows .multi-row');
    if (el.closest('.multi-row') === rows[rows.length - 1]) addStudentRow();
}

async function saveMultiStudents() {
    const rowEls = document.querySelectorAll('#multiAddRows .multi-row');
    const rows = [], errs = [], seen = new Set();
    rowEls.forEach((el, i) => {
        const sid   = el.querySelector('.m-id').value.trim();
        const name  = el.querySelector('.m-name').value.trim();
        const major = el.querySelector('.m-major').value;
        const pts   = parseInt(el.querySelector('.m-pts').value) || 0;
        el.style.background = '';
        if (!sid && !name) return;  // ข้ามแถวว่าง
        if (!sid || !name) { errs.push(`แถว ${i+1}: กรอกรหัสและชื่อให้ครบ`); el.style.background = '#fff3f3'; return; }
        if (seen.has(sid)) { errs.push(`แถว ${i+1}: รหัส ${sid} ซ้ำในฟอร์ม`);  el.style.background = '#fff3f3'; return; }
        seen.add(sid);
        rows.push({ student_id: sid, student_name: name, major_id: major || null, points_balance: pts });
    });

    const result = document.getElementById('multiAddResult');
    if (errs.length) {
        result.style.cssText = 'display:block;background:#f8d7da;border:1px solid #b43030;border-radius:8px;padding:12px;color:#721c24;font-size:14px;';
        result.innerHTML = '❌ ' + errs.join('<br>');
        return;
    }
    if (!rows.length) {
        result.style.cssText = 'display:block;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px;color:#856404;font-size:14px;';
        result.innerHTML = '⚠️ กรุณากรอกข้อมูลอย่างน้อย 1 คน';
        return;
    }

    const btn = document.getElementById('multiSaveBtn');
    btn.disabled = true; btn.textContent = '⏳ กำลังบันทึก...';
    try {
        const res = await fetch(`${API}?action=import_students`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ rows })
        }).then(r => r.json());
        if (res.success) {
            result.style.cssText = 'display:block;background:#d4edda;border:1px solid #14764e;border-radius:8px;padding:12px;color:#155724;font-size:14px;';
            result.innerHTML = `✅ เพิ่มนักศึกษาใหม่ <strong>${res.inserted} คน</strong>` +
                (res.updated ? ` · 🔄 อัพเดทข้อมูลเดิม <strong>${res.updated} คน</strong>` : '') +
                (res.errors && res.errors.length ? `<br>⚠️ ${res.errors.join('<br>')}` : '');
            document.getElementById('multiAddRows').innerHTML = '';
            addStudentRow(false);
            loadStudents();
        } else {
            result.style.cssText = 'display:block;background:#f8d7da;border:1px solid #b43030;border-radius:8px;padding:12px;color:#721c24;font-size:14px;';
            result.innerHTML = '❌ ' + res.message;
        }
    } catch(e) {
        result.style.cssText = 'display:block;background:#f8d7da;border:1px solid #b43030;border-radius:8px;padding:12px;color:#721c24;font-size:14px;';
        result.innerHTML = '❌ ไม่สามารถเชื่อมต่อได้';
    }
    btn.disabled = false; btn.textContent = '💾 บันทึกทั้งหมด';
}

function showEditStudentModal(studentId) {
    const s = allStudents.find(x => x.student_id===studentId);
    if (!s) return;
    document.getElementById('studentModalTitle').textContent = '✏️ แก้ไขข้อมูลนักศึกษา';
    document.getElementById('stuSaveBtn').textContent        = '💾 บันทึก';
    document.getElementById('stu_mode').value   = 'edit';
    document.getElementById('stu_id').value     = s.student_id;
    document.getElementById('stu_id').disabled  = true;
    document.getElementById('stu_name').value   = s.student_name;
    document.getElementById('stu_points_group').style.display = 'none';
    populateMajorSelect('stu_major', s.major_id);
    document.getElementById('studentModal').classList.add('show');
}

function populateMajorSelect(selId, currentMajorId=null) {
    const sel = document.getElementById(selId);
    sel.innerHTML = '<option value="">— เลือกสาขา —</option>';
    // จัดกลุ่มตามคณะ
    const facGroups = {};
    allMajors.forEach(m => {
        if (!facGroups[m.faculty_id]) facGroups[m.faculty_id] = { name: m.faculty_name, majors: [] };
        facGroups[m.faculty_id].majors.push(m);
    });
    Object.values(facGroups).forEach(g => {
        const grp = document.createElement('optgroup');
        grp.label = g.name;
        g.majors.forEach(m => {
            grp.innerHTML += `<option value="${m.major_id}">${m.major_abbr ? '['+m.major_abbr+'] ' : ''}${m.major_name}</option>`;
        });
        sel.appendChild(grp);
    });
    if (currentMajorId) sel.value = currentMajorId;
    // แสดงชื่อคณะอัตโนมัติ
    sel.onchange = function() {
        const m = allMajors.find(x => String(x.major_id) === String(this.value));
        const disp = document.getElementById('stu_faculty_display');
        if (m && disp) {
            document.getElementById('stu_faculty_name').textContent = m.faculty_name;
            disp.style.display = '';
        } else if (disp) {
            disp.style.display = 'none';
        }
    };
    // trigger แสดงคณะถ้ามีค่าเลือกอยู่แล้ว
    if (currentMajorId) sel.onchange.call(sel);
}

async function saveStudent() {
    const mode  = document.getElementById('stu_mode').value;
    const id    = document.getElementById('stu_id').value.trim();
    const name  = document.getElementById('stu_name').value.trim();
    const major = document.getElementById('stu_major').value;
    const pts   = parseInt(document.getElementById('stu_points').value) || 0;
    if (!id || !name) return alert('กรุณากรอกรหัสและชื่อ');
    const payload = { student_id:id, student_name:name, major_id:major||null };
    if (mode==='add') payload.points_balance = pts;
    try {
        const res = await fetch(`${API}?action=${mode==='add'?'add_student':'update_student'}`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
        }).then(r => r.json());
        if (res.success) { closeModal('studentModal'); showSuccess(mode==='add'?'เพิ่มนักศึกษาสำเร็จ':'แก้ไขข้อมูลสำเร็จ'); loadStudents(); }
        else alert('❌ '+res.message);
    } catch(e) { alert('ไม่สามารถเชื่อมต่อได้'); }
}

function showDeleteStudentModal(studentId) {
    const s = allStudents.find(x => x.student_id===studentId);
    if (!s) return;
    currentDeleteStudentId = studentId;
    document.getElementById('del_stu_name').textContent = s.student_name;
    document.getElementById('del_stu_id').textContent   = s.student_id;
    document.getElementById('del_stu_pts').textContent  = s.points_balance+' พอยต์';
    const warn   = document.getElementById('del_stu_warn');
    const delBtn = document.getElementById('delStuBtn');
    const reasons = [];
    if (parseInt(s.points_balance)>0)       reasons.push('• ยังมีพอยต์คงเหลือ '+s.points_balance+' พอยต์');
    if (parseInt(s.total_prints||0)>0)      reasons.push('• มีประวัติการพิมพ์ '+s.total_prints+' ครั้ง');
    if (reasons.length) {
        warn.innerHTML = '<strong>❌ ไม่สามารถลบได้</strong><br>'+reasons.join('<br>');
        warn.style.display = 'block'; delBtn.disabled = true; delBtn.style.opacity = '0.5';
    } else {
        warn.style.display = 'none'; delBtn.disabled = false; delBtn.style.opacity = '1';
    }
    document.getElementById('deleteStudentModal').classList.add('show');
}

async function confirmDeleteStudent() {
    try {
        const res = await fetch(`${API}?action=delete_student`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({student_id:currentDeleteStudentId})
        }).then(r => r.json());
        if (res.success) { closeModal('deleteStudentModal'); showSuccess('ลบนักศึกษาสำเร็จ'); loadStudents(); }
        else alert('❌ '+res.message);
    } catch(e) { alert('ไม่สามารถเชื่อมต่อได้'); }
}

// ─── Faculties ───────────────────────────────────────
async function loadFaculties() {
    const tbody = document.getElementById('facultiesBody');
    try {
        const res = await fetch(`${API}?action=get_faculties`).then(r => r.json());
        if (!res.success || !res.data || !res.data.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:30px;color:#999;">ยังไม่มีคณะ</td></tr>';
            return;
        }
        allFaculties = res.data;
        document.getElementById('totalFaculties').textContent = res.data.length;
        tbody.innerHTML = res.data.map(f => `<tr>
            <td><span class="badge badge-secondary">${f.faculty_id}</span></td>
            <td><strong>${f.faculty_name}</strong></td>
            <td style="text-align:center;">${f.major_count} สาขา</td>
            <td>
                <button onclick="showEditFacultyModal('${f.faculty_id}')" class="btn btn-primary btn-sm" style="padding:5px 9px;font-size:12px;">✏️ แก้ไข</button>
                <button onclick="deleteFacultyById('${f.faculty_id}','${f.faculty_name}')" class="btn btn-danger btn-sm" style="padding:5px 9px;font-size:12px;">🗑️</button>
            </td>
        </tr>`).join('');
        // อัพเดต dropdown items (ไม่ต้อง set innerHTML แล้ว เพราะใช้ dynamic dropdown)
        populateMajorFacultySelect();
    } catch(e) { tbody.innerHTML = '<tr><td colspan="4" style="color:red;text-align:center;padding:20px;">❌ โหลดไม่สำเร็จ</td></tr>'; }
}

function showAddFacultyModal() {
    document.getElementById('facultyModalTitle').textContent = '➕ เพิ่มคณะใหม่';
    document.getElementById('fac_id').value   = '';
    document.getElementById('fac_code').value = '';
    document.getElementById('fac_name').value = '';
    document.getElementById('fac_code').disabled = false;
    document.getElementById('facultyModal').classList.add('show');
}
function showEditFacultyModal(id) {
    const f = allFaculties.find(x => String(x.faculty_id) === String(id));
    if (!f) return;
    document.getElementById('facultyModalTitle').textContent = '✏️ แก้ไขคณะ';
    document.getElementById('fac_id').value   = f.faculty_id;
    document.getElementById('fac_code').value = f.faculty_id;   // รหัสคือ faculty_id
    document.getElementById('fac_code').disabled = true;         // ห้ามเปลี่ยนรหัส
    document.getElementById('fac_name').value = f.faculty_name;
    document.getElementById('facultyModal').classList.add('show');
}
async function saveFaculty() {
    const id   = document.getElementById('fac_id').value;
    const code = document.getElementById('fac_code').value.trim();
    const name = document.getElementById('fac_name').value.trim();
    if (!code || !name) return alert('กรุณากรอกข้อมูลให้ครบ');
    // เพิ่มใหม่ใช้ code เป็น faculty_id, แก้ไขใช้ id เดิม
    const payload = id
        ? { faculty_id: id,   faculty_name: name }
        : { faculty_id: code, faculty_name: name };
    try {
        const res = await fetch(`${API}?action=${id?'update_faculty':'add_faculty'}`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
        }).then(r => r.json());
        if (res.success) { closeModal('facultyModal'); showSuccess(res.message); loadFaculties(); }
        else alert('❌ '+res.message);
    } catch(e) { alert('ไม่สามารถเชื่อมต่อได้'); }
}
async function deleteFacultyById(id, name) {
    if (!confirm(`ลบคณะ "${name}" ?\n(จะลบสาขาทั้งหมดในคณะนี้ด้วย)`)) return;
    try {
        const res = await fetch(`${API}?action=delete_faculty`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({faculty_id:id})
        }).then(r => r.json());
        if (res.success) { showSuccess(res.message); loadFaculties(); loadMajors(); }
        else alert('❌ '+res.message);
    } catch(e) { alert('ไม่สามารถเชื่อมต่อได้'); }
}

// ─── Majors ──────────────────────────────────────────
async function loadMajors() {
    const tbody = document.getElementById('majorsBody');
    try {
        const res = await fetch(`${API}?action=get_majors`).then(r => r.json());
        if (!res.success || !res.data || !res.data.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:30px;color:#999;">ยังไม่มีสาขา</td></tr>';
            return;
        }
        allMajors = res.data;
        document.getElementById('totalMajors').textContent = res.data.length;
        tbody.innerHTML = res.data.map(m => `<tr>
            <td><span class="badge badge-info">${m.major_abbr || m.major_id}</span></td>
            <td><strong>${m.major_name}</strong></td>
            <td style="font-size:13px;color:#555;">${m.faculty_name}</td>
            <td style="text-align:center;">${m.student_count} คน</td>
            <td>
                <button onclick="showEditMajorModal('${m.major_id}')" class="btn btn-primary btn-sm" style="padding:5px 9px;font-size:12px;">✏️ แก้ไข</button>
                <button onclick="deleteMajorById('${m.major_id}','${m.major_name}')" class="btn btn-danger btn-sm" style="padding:5px 9px;font-size:12px;">🗑️</button>
            </td>
        </tr>`).join('');
    } catch(e) { tbody.innerHTML = '<tr><td colspan="5" style="color:red;text-align:center;padding:20px;">❌ โหลดไม่สำเร็จ</td></tr>'; }
}
function populateMajorFacultySelect() {
    const sel = document.getElementById('maj_faculty'); if (!sel) return;
    sel.innerHTML = '<option value="">— เลือกคณะ —</option>';
    allFaculties.forEach(f => sel.innerHTML += `<option value="${f.faculty_id}">${f.faculty_name}</option>`);
}
function showAddMajorModal() {
    document.getElementById('majorModalTitle').textContent = '➕ เพิ่มสาขาใหม่';
    document.getElementById('maj_id').value       = '';
    document.getElementById('maj_id_input').value = '';
    document.getElementById('maj_id_input').disabled = false;
    document.getElementById('maj_abbr').value     = '';
    document.getElementById('maj_name').value     = '';
    populateMajorFacultySelect();
    document.getElementById('majorModal').classList.add('show');
}
function showEditMajorModal(id) {
    const m = allMajors.find(x => String(x.major_id) === String(id));
    if (!m) return;
    document.getElementById('majorModalTitle').textContent = '✏️ แก้ไขสาขา';
    document.getElementById('maj_id').value          = m.major_id;
    document.getElementById('maj_id_input').value    = m.major_id;
    document.getElementById('maj_id_input').disabled = true;   // ห้ามเปลี่ยนรหัส
    document.getElementById('maj_abbr').value        = m.major_abbr || '';
    document.getElementById('maj_name').value        = m.major_name;
    populateMajorFacultySelect();
    document.getElementById('maj_faculty').value = m.faculty_id;
    document.getElementById('majorModal').classList.add('show');
}
async function saveMajor() {
    const id    = document.getElementById('maj_id').value;
    const newId = document.getElementById('maj_id_input').value.trim();
    const facId = document.getElementById('maj_faculty').value;
    const abbr  = document.getElementById('maj_abbr').value.trim().toUpperCase();
    const name  = document.getElementById('maj_name').value.trim();
    if (!facId || !newId || !name) return alert('กรุณากรอกข้อมูลให้ครบ');
    const payload = {
        faculty_id: facId,
        major_abbr: abbr,
        major_name: name,
        major_id:   id || newId   // แก้ไขใช้ id เดิม, เพิ่มใหม่ใช้ newId
    };
    try {
        const res = await fetch(`${API}?action=${id?'update_major':'add_major'}`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
        }).then(r => r.json());
        if (res.success) { closeModal('majorModal'); showSuccess(res.message); loadMajors(); }
        else alert('❌ '+res.message);
    } catch(e) { alert('ไม่สามารถเชื่อมต่อได้'); }
}
async function deleteMajorById(id, name) {
    if (!confirm(`ลบสาขา "${name}" ?`)) return;
    try {
        const res = await fetch(`${API}?action=delete_major`, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({major_id:id})
        }).then(r => r.json());
        if (res.success) { showSuccess(res.message); loadMajors(); }
        else alert('❌ '+res.message);
    } catch(e) { alert('ไม่สามารถเชื่อมต่อได้'); }
}

// ─── Utils ───────────────────────────────────────────
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
function showSuccess(msg) {
    document.getElementById('successMsg').textContent = msg;
    document.getElementById('successModal').classList.add('show');
}

// ─── Debt Filter Toggle ──────────────────────────────
function toggleDebtFilter() {
    debtFilterActive = !debtFilterActive;
    const btn = document.getElementById('btnDebtFilter');
    btn.style.background  = debtFilterActive ? '#991b1b' : '#ef4444';
    btn.style.boxShadow   = debtFilterActive ? '0 0 0 3px rgba(239,68,68,.35)' : '';
    btn.style.fontWeight  = debtFilterActive ? '700' : '500';
    loadStudents();
}

// ─── Init ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    await loadFaculties();
    await loadMajors();
    // ตรวจ query string ?filter=debt (มาจาก dashboard)
    const params = new URLSearchParams(window.location.search);
    if (params.get('filter') === 'debt') {
        debtFilterActive = true;
        // จะ sync style หลัง loadStudents โหลด debt count แล้ว
    }
    loadStudents();
    // sync style ปุ่มหลัง init
    if (debtFilterActive) {
        const btn = document.getElementById('btnDebtFilter');
        btn.style.background = '#991b1b';
        btn.style.boxShadow  = '0 0 0 3px rgba(239,68,68,.35)';
        btn.style.fontWeight = '700';
    }
});
window.onclick = e => { if (e.target.classList.contains('modal')) e.target.classList.remove('show'); };

// ════════════════════════════════════════════════════
// IMPORT EXCEL
// ════════════════════════════════════════════════════
let importRows = [];

function showImportModal() {
    importRows = [];
    document.getElementById('importPreview').style.display   = 'none';
    document.getElementById('importResult').style.display    = 'none';
    document.getElementById('importBtn').style.display       = 'none';
    document.getElementById('importColInfo').style.display   = 'none';
    document.getElementById('excelFile').value               = '';
    document.getElementById('dropzoneText').textContent      = 'คลิกหรือลากไฟล์ Excel มาวาง';
    populateImportFacultySelect();
    document.getElementById('importModal').classList.add('show');
}

function populateImportFacultySelect() {
    const sel = document.getElementById('importFaculty');
    sel.innerHTML = '<option value="">— ไม่ระบุคณะ —</option>';
    allFaculties.forEach(f => sel.innerHTML += `<option value="${f.faculty_id}">${f.faculty_name}</option>`);
    document.getElementById('importMajor').innerHTML = '<option value="">— ไม่ระบุสาขา —</option>';
}

function onImportFacultyChange() {
    const facId = document.getElementById('importFaculty').value;
    const sel   = document.getElementById('importMajor');
    sel.innerHTML = '<option value="">— ไม่ระบุสาขา —</option>';
    allMajors.filter(m => !facId || String(m.faculty_id)===String(facId))
             .forEach(m => sel.innerHTML += `<option value="${m.major_id}">${m.major_name}</option>`);
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('importDropzone').style.background  = '#f8f9ff';
    document.getElementById('importDropzone').style.borderColor = '#003087';
    const file = e.dataTransfer.files[0];
    if (file) processExcelFile(file);
}
function handleFileSelect(input) {
    if (input.files[0]) processExcelFile(input.files[0]);
}

// แปลงชื่อคอลัมน์ให้เป็นมาตรฐาน (ลบช่องว่าง/ขีด/จุด/ตัวพิมพ์เล็ก)
function normalizeKey(k) {
    return String(k).toLowerCase().replace(/[\s\-_.]/g, '');
}

// ค้นหาค่าจาก candidates หลายรูปแบบ
function findCol(row, candidates) {
    for (const cand of candidates) {
        const nc = normalizeKey(cand);
        for (const key of Object.keys(row)) {
            if (normalizeKey(key) === nc) return row[key];
        }
    }
    // ถ้ายังไม่เจอ ลอง partial match
    for (const cand of candidates) {
        const nc = normalizeKey(cand);
        for (const key of Object.keys(row)) {
            const nk = normalizeKey(key);
            if (nk.includes(nc) || nc.includes(nk)) return row[key];
        }
    }
    return '';
}

// แก้ปัญหา Excel เปลี่ยนรหัสนักศึกษาเป็น float (67010001.0)
function cleanId(val) {
    if (val === null || val === undefined || val === '') return '';
    if (typeof val === 'number') return String(Math.round(val));
    return String(val).trim().replace(/\.0+$/, '');
}

async function processExcelFile(file) {
    // ─── ไฟล์ .asp (RTF จากระบบมหาวิทยาลัย) ────────────────
    if (file.name.match(/\.asp$/i)) {
        return processRtfFile(file);
    }

    document.getElementById('dropzoneText').textContent = '⏳ กำลังอ่านไฟล์...';
    document.getElementById('importResult').style.display = 'none';

    try {
        const buffer = await file.arrayBuffer();
        const wb = XLSX.read(buffer, { type: 'array', codepage: 874 });
        const ws = wb.Sheets[wb.SheetNames[0]];

        // อ่านเป็น array 2 มิติก่อน เพื่อหา header row
        const raw = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });
        if (!raw.length) return setImportError('ไฟล์ Excel ว่างเปล่า');

        // หาแถว header (แถวแรกที่มีข้อมูลมากกว่า 1 ช่อง)
        let headerRowIdx = 0;
        for (let i = 0; i < Math.min(10, raw.length); i++) {
            const nonEmpty = raw[i].filter(c => String(c).trim() !== '').length;
            if (nonEmpty >= 2) { headerRowIdx = i; break; }
        }

        const headers = raw[headerRowIdx].map(h => String(h).trim());

        // แสดงคอลัมน์ที่พบ
        const colInfo = document.getElementById('importColInfo');
        colInfo.innerHTML = `📋 คอลัมน์ที่พบ: <strong>${headers.filter(h=>h).join('</strong>, <strong>')}</strong>`;
        colInfo.style.display = '';

        // แปลงเป็น array of objects
        const jsonRows = [];
        for (let i = headerRowIdx + 1; i < raw.length; i++) {
            if (raw[i].every(c => String(c).trim() === '')) continue; // skip empty row
            const obj = {};
            headers.forEach((h, j) => { if (h) obj[h] = raw[i][j] ?? ''; });
            jsonRows.push(obj);
        }

        // รายชื่อคอลัมน์ที่รองรับ
        const ID_COLS   = ['รหัสประจำตัว','รหัสนักศึกษา','student_id','รหัส','id','studentid','std_id','รหัสนักเรียน','รหัสประจำตัวนักศึกษา','เลขที่','no'];
        const NAME_COLS = ['ชื่อ','ชื่อ-สกุล','ชื่อสกุล','ชื่อ นามสกุล','student_name','name','fullname',
                           'ชื่อ-นามสกุล','ชื่อและนามสกุล','ชื่อนักศึกษา','studentname'];

        importRows = [];
        const skipped = [];

        jsonRows.forEach((row, i) => {
            const sid  = cleanId(findCol(row, ID_COLS));
            const name = String(findCol(row, NAME_COLS) || '').trim();
            if (!sid || !name) {
                if (sid || name) skipped.push(`แถว ${i + headerRowIdx + 2}`);
                return;
            }
            importRows.push({ student_id: sid, student_name: name, points_balance: 0, major_id: '' });
        });

        document.getElementById('dropzoneText').textContent = `✅ ${file.name}`;

        if (!importRows.length) {
            return setImportError(
                'ไม่พบข้อมูลนักศึกษา\n\n' +
                'คอลัมน์ที่พบ: ' + headers.filter(h=>h).join(', ') + '\n\n' +
                'ต้องมีคอลัมน์:\n' +
                '• รหัสนักศึกษา (หรือ student_id)\n' +
                '• ชื่อ-สกุล (หรือ student_name)'
            );
        }

        renderImportPreview();

        if (skipped.length) {
            colInfo.innerHTML += `<br>⚠️ ข้ามแถวที่ข้อมูลไม่ครบ: ${skipped.slice(0,5).join(', ')}${skipped.length>5?'...':''}`;
        }

    } catch(e) {
        document.getElementById('dropzoneText').textContent = 'คลิกหรือลากไฟล์ Excel มาวาง';
        setImportError('อ่านไฟล์ไม่สำเร็จ: ' + e.message);
    }
}

// แสดงตาราง preview ทุกแถว พร้อม dropdown สาขารายคน
function renderImportPreview() {
    const opts = majorOptionsHtml('— ไม่ระบุ —');
    document.getElementById('importPreviewBody').innerHTML = importRows.map((r, i) => `
        <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="padding:7px 12px;color:#999;">${i+1}</td>
            <td style="padding:7px 12px;"><strong>${r.student_id}</strong></td>
            <td style="padding:7px 12px;">${r.student_name}</td>
            <td style="padding:4px 8px;"><select class="row-major" onchange="importRows[${i}].major_id=this.value" style="width:100%;padding:4px 6px;border:1px solid #ddd;border-radius:5px;font-size:12px;">${opts}</select></td>
        </tr>`).join('');
    document.querySelectorAll('#importPreviewBody select.row-major').forEach((s, i) => s.value = importRows[i].major_id || '');
    document.getElementById('importRowCount').textContent  = `ทั้งหมด ${importRows.length} คน`;
    document.getElementById('importPreview').style.display = '';
    document.getElementById('importBtn').style.display     = '';
    document.getElementById('importCount').textContent     = importRows.length;
}

// เลือกสาขาด้านบน = ใช้กับทุกแถว (ยัง override รายคนได้)
function onImportMajorChange() {
    const v = document.getElementById('importMajor').value;
    importRows.forEach(r => r.major_id = v);
    document.querySelectorAll('#importPreviewBody select.row-major').forEach(s => s.value = v);
}

function setImportError(msg) {
    const el = document.getElementById('importResult');
    el.style.cssText = 'display:block;background:#f8d7da;border:1px solid #b43030;border-radius:8px;padding:14px;color:#721c24;white-space:pre-line;font-size:14px;';
    el.innerHTML = '❌ ' + msg;
    document.getElementById('importBtn').style.display = 'none';
    document.getElementById('dropzoneText').textContent = 'คลิกหรือลากไฟล์ Excel มาวาง';
}


// ─── ประมวลผลไฟล์ .asp (RTF) จากระบบมหาวิทยาลัย ──────
async function processRtfFile(file) {
    document.getElementById('dropzoneText').textContent = '⏳ กำลังอ่านไฟล์ RTF...';
    document.getElementById('importResult').style.display = 'none';

    try {
        const formData = new FormData();
        formData.append('file', file);

        const res = await fetch(API + '?action=parse_rtf_students', {
            method: 'POST',
            body: formData
        }).then(r => r.json());

        if (!res.success) {
            document.getElementById('dropzoneText').textContent = 'คลิกหรือลากไฟล์มาวาง';
            return setImportError(res.message);
        }

        importRows = res.data.map(r => ({ ...r, major_id: '' }));

        // แสดง column info
        const colInfo = document.getElementById('importColInfo');
        colInfo.innerHTML = `📋 อ่านไฟล์ RTF สำเร็จ: พบนักศึกษา <strong>${res.count} คน</strong>`;
        colInfo.style.display = '';

        renderImportPreview();
        document.getElementById('dropzoneText').textContent = `✅ ${file.name}`;

    } catch(e) {
        document.getElementById('dropzoneText').textContent = 'คลิกหรือลากไฟล์มาวาง';
        setImportError('อ่านไฟล์ไม่สำเร็จ: ' + e.message);
    }
}

async function doImport() {
    if (!importRows.length) return;
    const batchMajor = document.getElementById('importMajor').value || null;
    const rows       = importRows.map(r => ({ ...r, major_id: r.major_id || batchMajor }));

    const btn = document.getElementById('importBtn');
    btn.disabled    = true;
    btn.textContent = '⏳ กำลังนำเข้า...';

    try {
        const res = await fetch(API + '?action=import_students', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rows })
        }).then(r => r.json());

        const el = document.getElementById('importResult');
        if (res.success) {
            el.style.cssText = 'display:block;background:#d4edda;border:1px solid #14764e;border-radius:8px;padding:16px;color:#155724;font-size:14px;';
            el.innerHTML =
                `<strong style="font-size:16px;">✅ นำเข้าสำเร็จ!</strong><br><br>` +
                `➕ เพิ่มนักศึกษาใหม่: <strong>${res.inserted} คน</strong><br>` +
                `🔄 อัพเดทข้อมูลเดิม: <strong>${res.updated} คน</strong>` +
                (res.errors && res.errors.length
                    ? `<br><br><span style="color:#856404;">⚠️ ข้อผิดพลาด ${res.errors.length} รายการ:</span><br>${res.errors.slice(0,5).join('<br>')}`
                    : '');
            loadStudents();
        } else {
            el.style.cssText = 'display:block;background:#f8d7da;border:1px solid #b43030;border-radius:8px;padding:14px;color:#721c24;font-size:14px;';
            el.innerHTML = `❌ ${res.message}`;
        }
    } catch(e) {
        setImportError('ไม่สามารถเชื่อมต่อได้: ' + e.message);
    }

    btn.disabled = false;
    btn.innerHTML = `📥 นำเข้าข้อมูล &nbsp;<span id="importCount" style="background:rgba(255,255,255,.3);padding:2px 8px;border-radius:20px;">${importRows.length}</span> คน`;
}
</script>

<?php require_once '../../static/footer.php'; ?>