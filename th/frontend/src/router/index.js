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
import Cart from '../components/user/Cart.vue';
import axios from 'axios';

const routes = [
  { path: '/', component: Home },
  { path: '/cart', component: Cart, meta: { requiresAuth: true } },
  { path: '/infor', component: Infor },
  {
    path: '/admin/test',
    name: 'test',
    component: () => import('../components/user/EightProductGrid.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/gioi-thieu',
    name: 'gioi-thieu',
    component: () => import('../components/user/ISV.vue')
  },
  {
    path: '/Danh-muc-san-pham',
    name: 'DAnhMucSanPham',
    component: () => import('../components/user/DMSP.vue')
  },
  {
    path: '/so-sanh',
    name: 'ComparisonPage',
    component: () => import('../components/user/CompareProduct.vue')
  },
  {
    path: '/kich-hoat',
    name: 'KichHoat',
    component: () => import('../components/user/KichHoatTaiKhoan.vue')
  },
  {
    path: '/tin-tuc',
    name: 'TinTucCongKhai',
    component: () => import('../components/user/PublicNews.vue')
  },
  // Route cho danh mục con (tức danh mục cấp 2)
  {
    path: '/danh-muc/:id/:slug',
    name: 'CategoryProductsUser',
    component: () => import('../components/user/CategoryProductsPage.vue')
  },
  // Route MỚI cho danh mục cha (danh mục cấp 1)
  {
    path: '/danh-muc-cha/:id/:slug',
    name: 'ParentCategoryProductsUser',
    component: () => import('../components/user/CategoryProductsPage.vue'),
    props: route => ({ isParent: true })
  },
  {
    path: '/san-pham-ban-chay',
    name: 'SanPhamBanChay',
    component: () => import('../components/user/AllBestSellProduct.vue')
  },
  {
    path: '/tin-tuc-chi-tiet',
    name: 'ChiTietTinTucCongKhai',
    component: () => import('../components/user/NewsDetails.vue')
  },
  {
    path: '/tin-tuc-chi-tiet/:id',
    name: 'ChiTietTinTucCongKhaiId',
    component: () => import('../components/user/NewsDetails.vue')
  },
  { path: '/san-pham/:slug', name: 'ProductDetailUser', component: () => import('../components/user/ProductDetail.vue'),},
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
      
      { path: 'danh-muc-cap-2', name: 'MainCategoryLevel2List', component : () => import('@/components/admin/CategoryLevel2List.vue'), meta: { requiresAuth: true, role: 'admin' } },

      // Route đã được chuẩn hóa và loại bỏ trùng lặp
      {
        path: 'categories/:categoryId/children',
        name: 'ChildCategories',
        component: () => import('@/components/admin/CategoryLevel2List.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },

      { path: 'testt', component: () => import('../components/admin/Test.vue') },
      {
        path: 'orders',
        component: () => import('../components/admin/OrderList.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'send-push',
        name: 'SendPushNotification',
        component: () => import('../components/user/SendPushNotification.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'customers',
        component: () => import('../components/admin/CustomersList.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'danhmuctintuc',
        component: () => import('../components/admin/DmTinTuc.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'tintucold',
        component: () => import('../components/admin/Tintuc.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'tintuc',
        component: () => import('../components/admin/Tintucnew.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'slide',
        name: 'AdminSlide',
        component: () => import('../components/admin/SlideList.vue'),
        meta: { requiresAuth: true, role: 'admin' },
      },
      {
        path: 'products/:id/variants',
        name: 'ProductVariants',
        component: () => import('../views/admin/sanphambt/ProductVariants.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'products/:id/edit',
        component: () => import('../views/admin/EditProduct.vue')
      },
      {
        path: 'products/add',
        component: () => import('../views/admin/AddProduct.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'categories/:id/edit',
        component: () => import('../views/admin/categories/EditCategories.vue')
      },
      {
        path: 'danh-muc-tin-tuc/add',
        name: 'AddDmTinTuc',
        component: () => import('../views/admin/danhmuctt/Adddanhmuctt.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'danh-muc-tin-tuc/:id/edit',
        name: 'EditDmTinTuc',
        component: () => import('../views/admin/danhmuctt/Editdanhmuctt.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'danh-muc-tin-tuc/:id',
        name: 'XemDanhMucTinTuc',
        component: () => import('../views/admin/danhmuctt/Xemdanhmuctintic.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'tintuc/add',
        name: 'AddTintuc',
        component: () => import('../views/admin/Tintuc/Addtintuc.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'tintuc/:id/edit',
        name: 'EditTintuc',
        component: () => import('../views/admin/Tintuc/Edittintuc.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'tintuc/:id',
        name: 'XemTintuc',
        component: () => import('../views/admin/Tintuc/Xemtintuc.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'introduce',
        name: 'introduce',
        component: () => import('../components/admin/IntroduceList.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'trang-tinh/:slug',
        name: 'introduce-detail',
        component: () => import('../components/introduce/IntroduceDetail.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'trang-tinh/add',
        name: 'introduce-add',
        component: () => import('../components/introduce/IntroduceAdd.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'danh-muc-cap-2/add',
        name: 'AddCategoryLevel2',
        component: () => import('@/views/admin/danhmuccap2/Adddanhmuccap2.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      // Đã loại bỏ route trùng lặp và giữ lại route đúng
      {
        path: 'categories/:categoryId/children',
        name: 'ChildCategories',
        component: () => import('@/views/admin/danhmuccap2/ChildCategories.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'danh-muc-cap-2/edit/:id',
        name: 'EditCategoryLevel2',
        component: () => import('@/views/admin/danhmuccap2/Editdanhmuccap2.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'danh-muc-cap-2',
        name: 'CategoryLevel2List',
        component: () => import('@/components/admin/CategoryLevel2List.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'categories/add',
        name: 'AdminAddCategory',
        component: () => import('../views/admin/categories/AddCategories.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: 'categories/:category_id/products',
        name: 'CategoryProducts',
        component: () => import('../views/admin/categories/CategoriesProduct.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
    ]
  },
  {
    path: '/search',
    name: 'SearchResult',
    component: () => import('../views/SearchResult.vue'),
  },
  {
    path: '/product/:id',
    name: 'ProductDetail',
    component: () => import('@/components/user/ProductDetail.vue')
  },
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