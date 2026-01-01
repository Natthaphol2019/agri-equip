<template>
  <div style="font-family: 'Segoe UI', sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; background-color: #f8f9fa; min-height: 100vh;">
    
    <div v-if="!isLoggedIn" class="login-container fade-in">
        <div style="text-align:center; margin-bottom:30px;">
            <h1 style="font-size:3em;">🌱</h1>
            <h2 style="color:#2c3e50;">Agri-Equip Pro</h2>
            <p style="color:gray;">ระบบบริหารจัดการเครื่องจักรและงานบริการ</p>
        </div>
        
        <div class="card" style="max-width:400px; margin:0 auto; padding:40px;">
            <div style="margin-bottom:15px;">
                <label>ชื่อผู้ใช้ (Username):</label>
                <input v-model="loginForm.username" type="text" placeholder="เช่น admin หรือ staff" class="form-input">
            </div>
            <div style="margin-bottom:20px;">
                <label>รหัสผ่าน:</label>
                <input v-model="loginForm.password" type="password" placeholder="******" class="form-input" @keyup.enter="handleLogin">
            </div>
            <button @click="handleLogin" class="btn-green" style="width:100%; padding:12px;">🔒 เข้าสู่ระบบ</button>
            <p v-if="loginError" style="color:red; text-align:center; margin-top:10px;">{{ loginError }}</p>
        </div>
        
        <div style="text-align:center; margin-top:20px; color:#aaa; font-size:0.9em;">
            <p>Demo Account:</p>
            <p>Admin: admin / 123456</p>
            <p>Staff: staff / 123456</p>
        </div>
    </div>

    <div v-else>
        <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="display:flex; align-items:center; gap:10px;">
                <h2 style="margin:0; color:#2c3e50;">🌱 Agri-Equip</h2>
                <div style="display:flex; flex-direction:column;">
                    <span style="font-size:0.9em; font-weight:bold;">{{ currentUser.name }}</span>
                    <span class="badge" :style="{background: currentRole==='admin'?'orange':'#17a2b8'}">{{ currentRole.toUpperCase() }}</span>
                </div>
            </div>
            
            <div style="display:flex; gap:10px;">
                <button v-if="currentRole==='admin'" @click="changeView('dashboard')" :class="{active: view==='dashboard'}">📊 ภาพรวม</button>
                <button v-if="currentRole==='admin'" @click="changeView('admin')" :class="{active: view==='admin'}">👨‍💼 ตรวจสอบงาน</button>
                <button v-if="currentRole==='admin'" @click="changeView('customers')" :class="{active: view==='customers'}">👥 ลูกค้า</button>
                
                <button v-if="currentRole==='staff'" @click="changeView('staff')" :class="{active: view==='staff'}">👷‍♂️ งานของฉัน</button>
                <button @click="changeView('equipment')" :class="{active: view==='equipment'}">🚜 เครื่องจักร</button>
                <button @click="logout" style="background:#dc3545; color:white;">🚪 ออก</button>
            </div>
        </div>

        <CustomerManager v-if="view === 'customers'" />

        <EquipmentManager v-if="view === 'equipment'" :current-role="currentRole" />

        <div v-if="view === 'dashboard'" class="fade-in">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2>📊 สรุปภาพรวมระบบ</h2>
                <button @click="fetchDashboard" style="background:white; color:#333; border:1px solid #ddd;">🔄 รีเฟรช</button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                <div class="stat-card" style="border-left: 5px solid #28a745;">
                    <h3>💰 {{ formatCurrency(stats.total_revenue) }}</h3><p>รายได้รวม (บาท)</p>
                </div>
                <div class="stat-card" style="border-left: 5px solid #007bff;">
                    <h3>✅ {{ stats.completed_jobs }}</h3><p>งานเสร็จสิ้น</p>
                </div>
                <div class="stat-card" style="border-left: 5px solid #ffc107;">
                    <h3>⏳ {{ stats.pending_jobs }}</h3><p>รอตรวจสอบ</p>
                </div>
                <div class="stat-card" style="border-left: 5px solid #dc3545;">
                    <h3>🛠️ {{ stats.maintenance_machines }}</h3><p>กำลังซ่อม</p>
                </div>
            </div>
        </div>

        <div v-if="view === 'staff'" class="fade-in">
            <h2>👷‍♂️ รายการงานของฉัน</h2>
            <div v-for="job in myJobs" :key="job.id" class="card">
                <h3>ลูกค้า: {{ job.customer.name }} <span class="badge" :class="job.status">{{ job.status }}</span></h3>
                <p>🗓️ เริ่ม: {{ formatDate(job.scheduled_start) }} | สิ้นสุด: {{ formatDate(job.scheduled_end) }}</p>
                <button v-if="job.status === 'scheduled'" @click="startJob(job.id)" class="btn-green" style="width:100%;">▶ เริ่มงาน</button>
                <div v-if="job.status === 'in_progress'" style="margin-top:15px; background:#f8f9fa; padding:15px; border-radius:8px;">
                    <p>📸 ถ่ายรูปหลักฐาน:</p>
                    <input type="file" @change="handleFileUpload($event, job.id)" accept="image/*" style="width:100%; margin-bottom:10px;">
                    <button @click="finishJob(job.id)" class="btn-blue" style="width:100%;">📤 ส่งงาน</button>
                </div>
            </div>
            <p v-if="myJobs.length === 0" class="empty-state">🎉 ไม่มีงานค้างครับ</p>
        </div>

        <div v-if="view === 'admin'" class="fade-in">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 class="section-title">⏳ งานรอตรวจสอบ</h3>
                <button @click="prepareCreateJob" class="btn-blue">+ เพิ่มงานใหม่ (Create Job)</button>
            </div>

            <div v-for="job in adminJobs" :key="'pending-'+job.id" class="card" style="border-left: 5px solid orange; display:flex; justify-content:space-between; align-items:center;">
                <div style="flex-grow:1;">
                    <h4>Job #{{ job.id }} - {{ job.customer.name }}</h4>
                    <p style="font-size:0.9em; color:gray; margin:0;">
                        คนขับ: {{ job.assigned_staff ? job.assigned_staff.name : 'ไม่ระบุ' }}
                    </p>
                    <div v-if="job.activities && job.activities.length">
                        <span style="font-size:0.8em; color:green;">(มีรูปส่งงานแล้ว)</span>
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <button @click="approveJob(job.id)" class="btn-orange">✅ อนุมัติ</button>
                    <button @click="deleteJob(job.id)" class="btn-red">🗑️ ลบ</button>
                </div>
            </div>
            <p v-if="adminJobs.length === 0" style="color:#aaa; font-style:italic; margin-bottom:30px;">- ไม่มีงานรอตรวจสอบ -</p>

            <h3 class="section-title">✅ ประวัติงาน (History)</h3>
            <div v-for="job in historyJobs" :key="'hist-'+job.id" class="card history-card">
                <div>
                    <h4 style="margin:0;">Job #{{ job.id }} - {{ job.customer.name }}</h4>
                    <p style="margin:5px 0; color:gray; font-size:0.9em;">
                        🚜 {{ job.equipment.name }} | 💰 <strong>{{ formatCurrency(job.total_price) }} บาท</strong>
                    </p>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <a :href="'/api/admin/jobs/'+job.id+'/receipt'" target="_blank" class="btn-print">🖨️</a>
                    <button @click="deleteJob(job.id)" class="btn-red" style="padding:5px 10px;">🗑️</button>
                </div>
            </div>
            <button @click="fetchHistory" style="width:100%; margin-top:10px; background:#eee; color:#555;">📥 โหลดเพิ่ม</button>
        </div>

        <div v-if="showJobModal" class="modal-overlay">
            <div class="modal-content" style="max-width:600px;">
                <h3>➕ สร้างงานใหม่ (Create Job)</h3>
                
                <div style="margin-bottom:10px;">
                    <label>เลือกเครื่องจักร:</label>
                    <select v-model="jobForm.equipment_id" class="form-input">
                        <option value="" disabled>-- เลือกเครื่องจักร --</option>
                        <option v-for="eq in equipments" :key="eq.id" :value="eq.id">
                            {{ eq.name }} ({{ eq.current_status }})
                        </option>
                    </select>
                </div>

                <div style="margin-bottom:10px;">
                    <label>มอบหมายพนักงาน (คนขับ):</label>
                    <select v-model="jobForm.assigned_staff_id" class="form-input">
                        <option value="" disabled>-- เลือกพนักงาน --</option>
                        <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
                            {{ staff.name }}
                        </option>
                    </select>
                </div>

                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>วันที่เริ่มงาน:</label>
                        <input v-model="jobForm.scheduled_start" type="datetime-local" class="form-input">
                    </div>
                    <div style="flex:1;">
                        <label>วันที่จบงาน (โดยประมาณ):</label>
                        <input v-model="jobForm.scheduled_end" type="datetime-local" class="form-input">
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <label>ราคาประเมิน (บาท):</label>
                    <input v-model="jobForm.total_price" type="number" class="form-input" placeholder="0.00">
                </div>

                <div v-if="jobError" style="margin-top:10px; padding:10px; background:#f8d7da; color:#721c24; border-radius:5px;">
                    ⚠️ {{ jobError }}
                </div>

                <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                    <button @click="showJobModal=false" style="background:#ccc; color:black;">ยกเลิก</button>
                    <button @click="saveJob" class="btn-blue">ยืนยันสร้างงาน</button>
                </div>
            </div>
        </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';
import CustomerManager from './components/CustomerManager.vue'; 
import EquipmentManager from './components/EquipmentManager.vue'; // ✅ 1. Import ไฟล์ใหม่

export default {
  // ✅ 2. ลงทะเบียน Component
  components: {
    CustomerManager,
    EquipmentManager 
  },
  data() {
    return {
      // Auth
      isLoggedIn: false, loginForm: { username: '', password: '' }, loginError: '', currentUser: {}, currentRole: '',
      // App Data
      view: 'dashboard', stats: {}, myJobs: [], adminJobs: [], historyJobs: [], files: {},
      staffList: [], 
      // ยังคง equipments ไว้เพื่อใช้ใน Dropdown ของ Create Job Modal
      equipments: [], 

      // Forms & Modals (สำหรับ Job)
      showJobModal: false, jobError: '', 
      jobForm: { 
          equipment_id: '', 
          assigned_staff_id: '', 
          scheduled_start: '', 
          scheduled_end: '', 
          total_price: 0,
          customer_id: 1 
      }
      // ❌ ลบตัวแปร eqForm, showEqModal ออกไปแล้ว (ไปอยู่ใน EquipmentManager)
    }
  },
  methods: {
    // --- Login Logic ---
    async handleLogin() {
        try { 
            const res = await axios.post('/api/login', this.loginForm); 
            if(res.data.success){ 
                this.currentUser=res.data.user; 
                this.currentRole=res.data.user.role || 'staff'; 
                this.isLoggedIn=true; 
                this.view = (this.currentRole==='admin'?'dashboard':'staff'); 
                this.loadAll(); 
            }
        } catch(e) { this.loginError='ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'; }
    },
    logout() { this.isLoggedIn=false; this.loginForm={username:'',password:''}; },

    // --- Load Data Helper ---
    changeView(v) { this.view = v; this.loadAll(); },
    
    loadAll() {
        if(this.view==='dashboard') axios.get('/api/admin/dashboard').then(r=>this.stats=r.data);
        if(this.view==='staff' && this.currentUser.id) axios.get(`/api/staff/${this.currentUser.id}/jobs`).then(r=>this.myJobs=r.data); 
        
        if(this.view==='admin') { 
            axios.get('/api/admin/jobs/pending').then(r=>this.adminJobs=r.data); 
            axios.get('/api/admin/jobs/completed').then(r=>this.historyJobs=r.data); 
            axios.get('/api/admin/staff-list').then(r => this.staffList = r.data);
            
            // โหลดเครื่องจักรไว้สำหรับ Dropdown
            axios.get('/api/equipments').then(r=>this.equipments=r.data);
        }
    },

    // --- CRUD JOBS (ADMIN) ---
    prepareCreateJob() {
        this.jobForm = { equipment_id: '', assigned_staff_id: '', scheduled_start: '', scheduled_end: '', total_price: '', customer_id: 1 };
        this.jobError = '';
        this.showJobModal = true;
        // โหลดข้อมูลล่าสุดเพื่อให้ Dropdown เป็นปัจจุบัน
        axios.get('/api/equipments').then(r=>this.equipments=r.data);
        axios.get('/api/admin/staff-list').then(r=>this.staffList=r.data);
    },

    async saveJob() {
        if(!this.jobForm.equipment_id || !this.jobForm.assigned_staff_id || !this.jobForm.scheduled_start || !this.jobForm.scheduled_end) {
            this.jobError = 'กรุณากรอกข้อมูลให้ครบถ้วน (เครื่องจักร, พนักงาน, เวลาเริ่ม-จบ)';
            return; 
        }
        try { 
            await axios.post('/api/admin/jobs', this.jobForm); 
            this.showJobModal = false; 
            this.loadAll(); 
            alert('✅ สร้างงานสำเร็จ! ระบบได้ตรวจสอบคิวงานเรียบร้อยแล้ว'); 
        } catch(e) { 
            console.error(e); 
            let errorMsg = 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ';
            if (e.response && e.response.data) {
                if (e.response.data.message) errorMsg = e.response.data.message;
                if (e.response.data.error) errorMsg += ' \n(รายละเอียด: ' + e.response.data.error + ')';
            }
            this.jobError = errorMsg; 
            alert('❌ ' + errorMsg); 
        }
    },
    async deleteJob(id) {
        if(!confirm('ยืนยันลบงานนี้?')) return;
        try { await axios.delete(`/api/admin/jobs/${id}`); this.loadAll(); } catch(e){ alert('Error'); }
    },

    // --- BUSINESS LOGIC ---
    async startJob(id) { if(confirm('ยืนยันเริ่มงาน?')) { await axios.post(`/api/staff/jobs/${id}/start`, {user_id:this.currentUser.id}); this.loadAll(); }},
    async finishJob(id) { 
        if(!this.files[id]) return alert('กรุณาแนบรูป'); 
        let fd = new FormData(); fd.append('user_id',this.currentUser.id); fd.append('images[]',this.files[id]); 
        await axios.post(`/api/staff/jobs/${id}/finish`, fd); this.loadAll(); 
    },
    handleFileUpload(e, id){ this.files[id] = e.target.files[0]; },
    async approveJob(id) { if(confirm('ยืนยันอนุมัติ?')) { await axios.post(`/api/admin/jobs/${id}/approve`, {user_id:this.currentUser.id}); this.loadAll(); }},
    
    // Helpers
    formatCurrency(v) { return new Intl.NumberFormat('th-TH').format(v || 0); },
    formatDate(d) { if(!d) return '-'; return new Date(d).toLocaleString('th-TH'); }
  }
}
</script>

<style>
/* Basics */
.form-input { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 1rem; }
.login-container { display: flex; flex-direction: column; justify-content: center; height: 80vh; }
button { cursor:pointer; border:none; border-radius:6px; padding:8px 15px; font-weight:600; transition:0.2s; }
button:hover { transform:translateY(-2px); opacity:0.9; }
button.active { background:#2c3e50 !important; color:white !important; }
button:not(.active) { background:#ecf0f1; color:#7f8c8d; }
.card, .stat-card { background:white; padding:20px; border-radius:12px; margin-bottom:15px; box-shadow:0 4px 10px rgba(0,0,0,0.03); border:1px solid #f1f1f1; }
.badge { padding:4px 8px; border-radius:12px; font-size:0.8em; color:white; margin-left:5px; }
.badge-green { background:#28a745; color:white; padding:2px 8px; border-radius:10px; font-size:0.8em; }
.badge-red { background:#dc3545; color:white; padding:2px 8px; border-radius:10px; font-size:0.8em; }
.section-title { background:#eef2f5; padding:10px 15px; border-radius:8px; color:#555; margin-bottom:15px; }

/* Buttons Color */
.btn-green { background:#27ae60 !important; color:white; }
.btn-blue { background:#3498db !important; color:white; }
.btn-orange { background:#f39c12 !important; color:white; }
.btn-red { background:#e74c3c !important; color:white; }
.btn-print { background:#34495e; color:white; text-decoration:none; padding:8px 15px; border-radius:6px; display:inline-block; }

/* Modal Styles */
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:30px; border-radius:12px; width:90%; max-width:500px; box-shadow:0 10px 25px rgba(0,0,0,0.2); }
.fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
</style>