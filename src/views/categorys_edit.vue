<template>
  <div class="container mt-4">
    <h2 class="mb-3">📦 รายการประเภทสินค้า</h2>

    <!-- 🔹 ปุ่มเพิ่ม + ตัวเลือกแถว + ตัวกรองประเภท -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <button class="btn btn-primary" @click="openAddModal">เพิ่มประเภทสินค้า +</button>

      <div class="d-flex align-items-center">
        <label class="me-2 fw-bold">ประเภทสินค้า:</label>
        <select v-model="selectedCategory" class="form-select w-auto">
          <option value="">ทั้งหมด</option>
          <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_name">
            {{ cat.category_name }}
          </option>
        </select>
      </div>

      <div class="d-flex align-items-center">
        <label class="me-2 fw-bold">แถวต่อหน้า:</label>
        <select v-model.number="itemsPerPage" class="form-select w-auto">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
        </select>
      </div>
    </div>

    <!-- ✅ ตารางสินค้า -->
    <table class="table table-bordered table-striped">
      <thead class="table-primary text-center">
        <tr>
          <th>ID</th>
          
          <th>ประเภทสินค้า</th>
          <th>การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="category in paginatedProducts" :key="category.category_id">
          <td>{{ category.category_id }}</td>
          <td>{{ category.category_name }}</td>
          

          
          <td>
            <div class="text-center">
            <button class="btn btn-warning btn-sm me-2" @click="openEditModal(category)">
              <i class="bi bi-pencil-square"></i>แก้ไข
            </button>
            <button class="btn btn-danger btn-sm" @click="deleteProduct(category.category_id)">
              <i class="bi bi-trash3"></i>ลบ
            </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center">⏳ กำลังโหลดข้อมูล...</div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- ✅ Pagination -->
    <nav v-if="totalPages > 1" class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="prevPage">ก่อนหน้า</button>
        </li>
        <li
          v-for="page in totalPages"
          :key="page"
          class="page-item"
          :class="{ active: currentPage === page }"
        >
          <button class="page-link" @click="goToPage(page)">{{ page }}</button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="nextPage">ถัดไป</button>
        </li>
      </ul>
    </nav>

    <!-- ✅ Modal เพิ่ม/แก้ไขสินค้า -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditMode ? "แก้ไขประเภทสินค้า" : "เพิ่มประเภทสินค้าใหม่" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveProduct">
              <div class="mb-3">
                <label class="form-label">ประเภทสินค้า</label>
                <input v-model="editForm.category_name" type="text" class="form-control" required />
              </div>
              
                
              
             
              <button type="submit" class="btn btn-success w-100">
                {{ isEditMode ? "บันทึกการแก้ไข" : "เพิ่มประเภทสินค้า" }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from "vue";

export default {
  name: "ProductList",
  setup() {
    const categorys = ref([]);
    const categories = ref([]);
    const selectedCategory = ref("");
    const loading = ref(true);
    const error = ref(null);

    // Modal & Form
    const isEditMode = ref(false);
    const editForm = ref({ category_id: null, category_name: "", category_id: "" });
    const newImageFile = ref(null);
    let modalInstance = null;

    // Pagination
    const currentPage = ref(1);
    const itemsPerPage = ref(5);

    const filteredProducts = computed(() => {
      if (!selectedCategory.value) return categorys.value;
      return categorys.value.filter((p) => p.category_name === selectedCategory.value);
    });

    const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value));
    const paginatedProducts = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      return filteredProducts.value.slice(start, start + itemsPerPage.value);
    });

    // ✅ ดึงข้อมูลสินค้า + ประเภทสินค้า
    const fetchProducts = async () => {
      try {
        const res = await fetch("http://localhost/mkshop_68701390/php_api/api_categorys.php");
        const data = await res.json();
        if (data.success) {
          categorys.value = data.data;
          categories.value = data.categories;
        } else {
          error.value = "ไม่สามารถดึงข้อมูลสินค้าได้";
        }
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    // ✅ เปิด modal เพิ่มสินค้า
    const openAddModal = () => {
      isEditMode.value = false;
      editForm.value = { category_id: null, category_name: "" };
      newImageFile.value = null;

      // เคลียร์ค่าไฟล์ input
      const fileInput = document.querySelector('#editModal input[type="file"]');
      if (fileInput) fileInput.value = "";

      const modalEl = document.getElementById("editModal");
      modalInstance = new window.bootstrap.Modal(modalEl);
      modalInstance.show();
    };

    // ✅ เปิด modal แก้ไขสินค้า
    const openEditModal = (category) => {
      isEditMode.value = true;
      editForm.value = { ...category, category_id: category.category_id };
      newImageFile.value = null;

      // เคลียร์ input file
      const fileInput = document.querySelector('#editModal input[type="file"]');
      if (fileInput) fileInput.value = "";

      const modalEl = document.getElementById("editModal");
      modalInstance = new window.bootstrap.Modal(modalEl);
      modalInstance.show();
    };

    const handleFileUpload = (e) => (newImageFile.value = e.target.files[0]);

    // ✅ บันทึกสินค้า (เพิ่ม/แก้ไข)
    const saveProduct = async () => {
      const formData = new FormData();
      formData.append("action", isEditMode.value ? "update" : "add");
      if (isEditMode.value) formData.append("category_id", editForm.value.category_id);
      formData.append("category_name", editForm.value.category_name);
   
      formData.append("category_id", editForm.value.category_id);
      if (newImageFile.value) formData.append("image", newImageFile.value);

      try {
        const res = await fetch("http://localhost/mkshop_68701390/php_api/api_categorys.php", { method: "POST", body: formData });
        const result = await res.json();
        alert(result.message || result.error);

        await fetchProducts();
        modalInstance.hide();

        // ✅ เคลียร์ค่าฟอร์มและรูปหลังบันทึก
        editForm.value = { category_id: null, category_name: "", category_id: "" };
        newImageFile.value = null;
        const fileInput = document.querySelector('#editModal input[type="file"]');
        if (fileInput) fileInput.value = "";

      } catch (error) {
        alert("เกิดข้อผิดพลาด: " + error.message);
      }
    };

    // ✅ ลบสินค้า
    const deleteProduct = async (id) => {
      if (!confirm("ยืนยันการลบประเภทสินค้านี้หรือไม่?")) return;
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("category_id", id);
      const res = await fetch("http://localhost/mkshop_68701390/php_api/api_categorys.php", { method: "POST", body: formData });
      const result = await res.json();
      alert(result.message || result.error);
      fetchProducts();
    };

    // ✅ Pagination
    const goToPage = (page) => (currentPage.value = page);
    const prevPage = () => (currentPage.value = Math.max(1, currentPage.value - 1));
    const nextPage = () => (currentPage.value = Math.min(totalPages.value, currentPage.value + 1));
    watch(itemsPerPage, () => (currentPage.value = 1));

    onMounted(fetchProducts);

    return {
      categorys, categories, selectedCategory, loading, error,
      isEditMode, editForm, openAddModal, openEditModal, saveProduct, deleteProduct, handleFileUpload,
      paginatedProducts, totalPages, currentPage, itemsPerPage, goToPage, prevPage, nextPage
    };
  }
};
</script>
