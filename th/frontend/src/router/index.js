import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home from '../views/Home.vue';
import Infor from '../components/user/InforUser.vue';
import AdminView from '../views/Admin.vue';
import UserAccountLayout from '../components/user/UserAccountLayout.vue'; 
import UserProfile from '../components/user/UserProfile.vue'; 
import UserOrders from '../components/user/UserOrders.vue';
import ChangePassword from '../components/user/ChangePassword.vue';
import axios from 'axios';

const routes = [
  { path: '/', component: Home },
  // { path: '/infor', component: Infor },
  {
    path: '/admin/test',
    name: 'test',
    component: () => import('../components/user/BestSellProduct.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/gioi-thieu',
    name: 'gioi-thieu',
    component: () => import('../components/user/ISV.vue')
  },
  {
    path: '/kich-hoat',
    name: 'KichHoat',
    component: () => import('../components/user/KichHoatTaiKhoan.vue')
  },
  {
    path: '/tin-tuc',
    name: 'TinTucCongKhai',
    component: () => import('../components/user/Publicnews.vue')
  },
  {
    path: '/user',
    component: UserAccountLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/user/profile' },
      { path: 'profile', name: 'UserProfile', component: UserProfile },
      { path: 'orders', name: 'UserOrders', component: UserOrders },
      { path: 'change-password', name: 'ChangePassword', component: ChangePassword }
    ]
  },
  {
    path: '/admin',
    component: AdminView,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', component: () => import('../views/admin/AdminDashboard.vue') },
      { path: 'products', component: () => import('../components/admin/ProductList.vue') },
      { path: 'category', component: () => import('../components/admin/CategoryList.vue') },
      { path: 'orders', component: () => import('../components/admin/OrderList.vue') },
      { path: 'customers', component: () => import('../components/admin/CustomersList.vue') },
      { path: 'danhmuctintuc', component: () => import('../components/admin/DmTinTuc.vue') },
      { path: 'tintuc', component: () => import('../components/admin/TinTuc.vue') },
      { path: 'slide', name: 'AdminSlide', component: () => import('../components/admin/SlideList.vue') },
      { path: 'products/:id/variants', name: 'ProductVariants', component: () => import('../views/admin/sanphambt/ProductVariants.vue') },
      { path: 'products/:id/edit', component: () => import('../views/admin/EditProduct.vue') },
      { path: 'products/add', component: () => import('../views/admin/AddProduct.vue') },
      { path: 'categories/:id/edit', component: () => import('../views/admin/categories/EditCategories.vue') },
      { path: 'danh-muc-tin-tuc/:id/edit', name: 'EditDmTinTuc', component: () => import('../views/admin/danhmuctt/Editdanhmuctt.vue') },
      { path: 'danh-muc-tin-tuc/add', name: 'AddDmTinTuc', component: () => import('../views/admin/danhmuctt/Adddanhmuctt.vue') },
      { path: 'danh-muc-tin-tuc/:id', name: 'XemDanhMucTinTuc', component: () => import('../views/admin/danhmuctt/Xemdanhmuctintic.vue') },
      { path: 'tintuc/add', name: 'AddTintuc', component: () => import('../views/admin/Tintuc/Addtintuc.vue') },
      { path: 'tintuc/:id/edit', name: 'EditTintuc', component: () => import('../views/admin/Tintuc/Edittintuc.vue') },
      { path: 'tintuc/:id', name: 'XemTintuc', component: () => import('../views/admin/Tintuc/Xemtintuc.vue') },
      { path: 'introduce', name: 'introduce', component: () => import('../components/admin/IntroduceList.vue') },
      { path: 'trang-tinh/:slug', name: 'introduce-detail', component: () => import('../components/introduce/IntroduceDetail.vue') },
      { path: 'trang-tinh/add', name: 'introduce-add', component: () => import('../components/introduce/IntroduceAdd.vue') }
    ]
  },
  { path: '/search', name: 'SearchResult', component: () => import('../views/SearchResult.vue') },
  { path: '/admin/products/:id', name: 'product-detail', component: () => import('../views/admin/ProductDetail.vue') },
  { path: '/admin/categories/add', component: () => import('../views/admin/categories/AddCategories.vue') },
  { path: '/admin/categories/:category_id/products', name: 'CategoryProducts', component: () => import('../components/admin/CategoriesProduct.vue') },
  { path: '/:pathMatch(.*)*', redirect: '/' }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('token');
  const requiresAuth = to.meta.requiresAuth;

  if (requiresAuth && !token) {
    return next('/login');
  }

  if (requiresAuth && token) {
    try {
      const response = await axios.get('/user', {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      const user = response.data;

      if (to.meta.role === 'admin' && user.vai_tro_id !== 1) {
        return next('/');
      }

      return next();
    } catch (err) {
      console.error('Lỗi xác thực:', err);
      localStorage.removeItem('token');
      return next('/login');
    }
  }

  return next();
});

export default router;
