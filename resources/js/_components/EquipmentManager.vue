<template>
  <div class="fade-in">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0; color:#2c3e50;">🚜 สถานะเครื่องจักร</h2>
        <button v-if="currentRole==='admin'" @click="prepareAddEquipment" class="btn-blue">+ เพิ่มเครื่องจักร</button>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        <div v-for="eq in equipments" :key="eq.id" class="card" :style="{borderTop: eq.current_status==='available' ? '5px solid #28a745' : '5px solid #dc3545'}">
            
            <div style="display:flex; justify-content:space-between;">
                <div>
                    <h3 style="margin:0;">{{ eq.name }}</h3>
                    <p style="font-size:0.8em; color:gray;">{{ eq.details }}</p>
                </div>
                
                <div v-if="currentRole==='admin'" style="display:flex; gap:5px;">
                    <button @click="prepareEditEquipment(eq)" style="background:#ffc107; color:black; padding:2px 8px; border:none; border-radius:4px; cursor:pointer;">✏️</button>
                    <button @click="deleteEquipment(eq.id)" style="background:#dc3545; color:white; padding:2px 8px; border:none; border-radius:4px; cursor:pointer;">🗑️</button>
                </div>
            </div>

            <div style="margin-top:10px;">
                <span :class="eq.current_status==='available' ? 'badge-green' : 'badge-red'">
                    {{ eq.current_status === 'available' ? 'พร้อมใช้งาน' : 'แจ้งซ่อม' }}
                </span>
            </div>

            <div v-if="eq.current_status === 'available'" style="margin-top:15px;">
                <button @click="reportIssue(eq.id)" class="btn-red" style="width:100%;">⚠️ แจ้งเครื่องจักรขัดข้อง</button>
            </div>

            <div v-if="eq.current_status === 'maintenance' && eq.active_maintenance" style="margin-top:15px; background:#fff3cd; padding:10px; border-radius:8px;">
                <p style="color:#856404; margin:0; font-weight:bold;">🛠️ กำลังซ่อมบำรุง</p>
                <p style="font-size:0.9em; margin:5px 0;">อาการ: {{ eq.active_maintenance.description }}</p>
                <hr style="border:0; border-top:1px dashed #ccc; margin:10px 0;">
                
                <div v-if="currentRole === 'admin'">
                    <p style="font-size:0.8em; color:green; margin-bottom:5px;">*Admin: ตรวจสอบและลงบันทึกค่าใช้จ่าย</p>
                    <button @click="completeRepair(eq.active_maintenance.id)" class="btn-green" style="width:100%;">
                        💰 บันทึกยอดเงิน & ปิดงาน
                    </button>
                </div>
                <div v-else style="text-align:center; color:#856404; font-size:0.9em; border:1px dashed #856404; padding:5px; border-radius:4px;">
                    ⏳ รอผู้ดูแลระบบดำเนินการซ่อมแซม
                </div>
            </div>
        </div>
    </div>

    <div v-if="showEqModal" class="modal-overlay">
        <div class="modal-content">
            <h3>{{ eqForm.id ? '✏️ แก้ไขเครื่องจักร' : '➕ เพิ่มเครื่องจักรใหม่' }}</h3>
            <label>ชื่อเครื่องจักร:</label>
            <input v-model="eqForm.name" type="text" class="form-input">
            <label>รายละเอียด:</label>
            <input v-model="eqForm.details" type="text" class="form-input">
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button @click="showEqModal=false" style="background:#ccc; color:black; border:none; padding:8px 15px; border-radius:6px; cursor:pointer;">ยกเลิก</button>
                <button @click="saveEquipment" class="btn-green">บันทึก</button>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    // รับค่าจากไฟล์แม่ (App.vue) ว่าใครล็อกอินอยู่
    props: ['currentRole'], 
    
    data() {
        return {
            equipments: [],
            showEqModal: false, 
            eqForm: { id: null, name: '', details: '' }
        }
    },
    mounted() {
        this.fetchEquipments();
    },
    methods: {
        async fetchEquipments() {
            try {
                const res = await axios.get('/api/equipments');
                this.equipments = res.data;
            } catch(e) { console.error(e); }
        },

        // --- CRUD ---
        prepareAddEquipment() { this.eqForm={id:null, name:'', details:''}; this.showEqModal=true; },
        prepareEditEquipment(eq) { this.eqForm={id:eq.id, name:eq.name, details:eq.details}; this.showEqModal=true; },
        async saveEquipment() {
            try {
                if(this.eqForm.id) await axios.put(`/api/admin/equipments/${this.eqForm.id}`, this.eqForm); 
                else await axios.post('/api/admin/equipments', this.eqForm);
                this.showEqModal=false; this.fetchEquipments(); alert('บันทึกเรียบร้อย!');
            } catch(e){ alert('เกิดข้อผิดพลาดในการบันทึกเครื่องจักร'); }
        },
        async deleteEquipment(id) {
            if(!confirm('ยืนยันลบเครื่องจักรนี้?')) return;
            try { await axios.delete(`/api/admin/equipments/${id}`); this.fetchEquipments(); } catch(e){ alert('ลบไม่ได้ (อาจมีงานค้างอยู่)'); }
        },

        // --- Maintenance ---
        async reportIssue(eqId) {
            let d = prompt('ระบุอาการเสีย:'); 
            if(d) { 
                await axios.post('/api/maintenance/report', {equipment_id:eqId, type:'corrective', description:d}); 
                this.fetchEquipments(); 
            }
        },
        async completeRepair(logId) {
            let c = prompt('ค่าซ่อมจริง (บาท):'); 
            if(c) { 
                await axios.post(`/api/maintenance/${logId}/complete`, {total_cost:c, service_provider:'In-House', reset_hours:false}); 
                this.fetchEquipments(); 
            }
        }
    }
}
</script>

<style scoped>
/* Style เดิม */
.card { background:white; padding:20px; border-radius:12px; margin-bottom:15px; box-shadow:0 4px 10px rgba(0,0,0,0.03); border:1px solid #f1f1f1; }
.form-input { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
.btn-green { background:#27ae60 !important; color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; }
.btn-blue { background:#3498db !important; color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; }
.btn-red { background:#e74c3c !important; color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; }
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:30px; border-radius:12px; width:90%; max-width:500px; box-shadow:0 10px 25px rgba(0,0,0,0.2); }
.badge-green { background:#28a745; color:white; padding:2px 8px; border-radius:10px; font-size:0.8em; }
.badge-red { background:#dc3545; color:white; padding:2px 8px; border-radius:10px; font-size:0.8em; }
.fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
</style>