<template>
  <div>
    <h2>Danh mục cấp 2</h2>rations\Migration;
    <table>ate\Database\Schema\Blueprint;
      <thead>e\Support\Facades\Schema;
        <tr>
          <th>Tên danh mục</th>ion
          <th>Mô tả</th>
          <th>Ngày tạo</th>
          <th>Ngày sửa</th>
        </tr>
      </thead>ction up(): void
      <tbody>
        <tr v-for="cat in categories" :key="cat.id">eprint $table) {
          <td>{{ cat.ten_danh_muc }}</td>
          <td>{{ cat.mo_ta }}</td>nh_muc');
          <td>{{ cat.created_at || 'N/A' }}</td>
          <td>{{ cat.updated_at || 'N/A' }}</td>
        </tr>
      </tbody>
    </table>hêm cột liên kết vào bảng danh_muc_san_pham
  </div>Schema::table('danh_muc_san_pham', function (Blueprint $table) {
</template> $table->unsignedBigInteger('danh_muc_cha_id')->nullable()->after('category_id');
            $table->foreign('danh_muc_cha_id')->references('id')->on('danh_muc_cha')->onDelete('set null');
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';ble('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
const categories = ref([]););
                $table->string('name');
const getCategories = async () => {enable_type');
  const res = await axios.get('http://localhost:8000/api/danh-muc-cha');
  categories.value = res.data;('token', 80)->unique();
};              $table->string('abilities')->nullable();
                $table->timestamps();
const toggleStatus = async (id) => {
  await axios.put(`http://localhost:8000/api/danh-muc-cha/${id}/toggle-status`);





</script>onMounted(getCategories);};  getCategories(); // Refresh the list after toggling status    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('danh_muc_san_pham', function (Blueprint $table) {
            $table->dropForeign(['danh_muc_cha_id']);
            $table->dropColumn('danh_muc_cha_id');
        });
        Schema::dropIfExists('danh_muc_cha');
        Schema::dropIfExists('personal_access_tokens');
    }
};
