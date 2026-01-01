<template>
  <div class="fade-in">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0; color:#2c3e50;">👥 จัดการรายชื่อลูกค้า</h2>
        <button @click="openModal()" class="btn-blue">+ เพิ่มลูกค้าใหม่</button>
    </div>

    <div style="background:white; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.05); overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8f9fa; border-bottom:2px solid #eee; text-align:left;">
                    <th style="padding:15px;">ชื่อ-นามสกุล / ฟาร์ม</th>
                    <th style="padding:15px;">เบอร์โทร</th>
                    <th style="padding:15px;">ประเภท</th>
                    <th style="padding:15px;">ที่อยู่</th>
                    <th style="padding:15px; text-align:center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="c in customers" :key="c.id" style="border-bottom:1px solid #eee;">
                    <td style="padding:15px; font-weight:bold; color:#2c3e50;">{{ c.name }}</td>
                    <td style="padding:15px;">{{ c.phone }}</td>
                    <td style="padding:15px;">
                        <span class="badge" :style="{background: c.customer_type==='farm'?'#28a745':'#17a2b8'}">
                            {{ c.customer_type === 'farm' ? 'ฟาร์ม/บริษัท' : 'บุคคลธรรมดา' }}
                        </span>
                    </td>
                    <td style="padding:15px; color:#666;">{{ c.address || '-' }}</td>
                    <td style="padding:15px; text-align:center;">
                        <button @click="openModal(c)" style="background:#ffc107; color:black; margin-right:5px; padding:5px 10px; border-radius:4px; border:none; cursor:pointer;">✏️</button>
                        <button @click="deleteCustomer(c.id)" style="background:#dc3545; color:white; padding:5px 10px; border-radius:4px; border:none; cursor:pointer;">🗑️</button>
                    </td>
                </tr>
                <tr v-if="customers.length === 0">
                    <td colspan="5" style="padding:30px; text-align:center; color:gray;">ยังไม่มีข้อมูลลูกค้า</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="showModal" class="modal-overlay">
        <div class="modal-content">
            <h3>{{ form.id ? '✏️ แก้ไขข้อมูลลูกค้า' : '➕ เพิ่มลูกค้าใหม่' }}</h3>
            
            <label>ชื่อ-นามสกุล / ชื่อฟาร์ม:</label>
            <input v-model="form.name" type="text" class="form-input" placeholder="เช่น ลุงสมหมาย หรือ ไร่นาสวนผสม">
            
            <label>เบอร์โทรศัพท์:</label>
            <input v-model="form.phone" type="text" class="form-input" placeholder="08x-xxxxxxx">
            
            <label>ประเภทลูกค้า:</label>
            <select v-model="form.customer_type" class="form-input">
                <option value="individual">บุคคลธรรมดา</option>
                <option value="farm">ฟาร์มเกษตร / บริษัท</option>
            </select>

            <label>ที่อยู่ / จุดสังเกต:</label>
            <textarea v-model="form.address" class="form-input" rows="3" placeholder="รายละเอียดที่อยู่..."></textarea>

            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button @click="showModal=false" style="background:#ccc; color:black; border:none; padding:8px 15px; border-radius:6px; cursor:pointer;">ยกเลิก</button>
                <button @click="saveCustomer" class="btn-blue">บันทึก</button>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            customers: [],
            showModal: false,
            form: { id: null, name: '', phone: '', address: '', customer_type: 'individual' }
        }
    },
    mounted() {
        this.fetchCustomers();
    },
    methods: {
        async fetchCustomers() {
            try {
                const res = await axios.get('/api/admin/customers');
                this.customers = res.data;
            } catch (e) {
                console.error(e);
            }
        },
        openModal(customer = null) {
            if (customer) {
                this.form = { ...customer };
            } else {
                this.form = { id: null, name: '', phone: '', address: '', customer_type: 'individual' };
            }
            this.showModal = true;
        },
        async saveCustomer() {
            if (!this.form.name || !this.form.phone) return alert('กรุณากรอกชื่อและเบอร์โทร');
            
            try {
                if (this.form.id) {
                    await axios.put(`/api/admin/customers/${this.form.id}`, this.form);
                } else {
                    await axios.post('/api/admin/customers', this.form);
                }
                this.showModal = false;
                this.fetchCustomers();
                alert('บันทึกสำเร็จ!');
            } catch (e) {
                alert('เกิดข้อผิดพลาด: ' + (e.response?.data?.message || e.message));
            }
        },
        async deleteCustomer(id) {
            if (!confirm('ยืนยันลบลูกค้ารายนี้?')) return;
            try {
                await axios.delete(`/api/admin/customers/${id}`);
                this.fetchCustomers();
            } catch (e) {
                alert('ลบไม่ได้ (อาจมีงานจองค้างอยู่)');
            }
        }
    }
}
</script>

<style scoped>
.form-input { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
.btn-blue { background:#3498db !important; color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; }
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:30px; border-radius:12px; width:90%; max-width:500px; box-shadow:0 10px 25px rgba(0,0,0,0.2); }
.badge { padding:4px 8px; border-radius:12px; font-size:0.8em; color:white; display:inline-block; }
.fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
</style>