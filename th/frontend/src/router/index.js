import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home from '../views/Home.vue';
import Infor from '../components/user/InforUser.vue';
import AdminView from '../views/Admin.vue';
import axios from 'axios';


const routes = [
  { path: '/', component: Home },
  { path: '/infor', component: Infor },
  {
    path: '/kich-hoat',
    name: 'KichHoat',
    component: () => import('../components/user/KichHoatTaiKhoan.vue')
  },


  {
    path: '/admin',
    component: AdminView,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', component: () => import('../views/admin/AdminDashboard.vue') },
      { path: 'products', component: () => import('../components/admin/ProductList.vue') },
      { path: 'category', component: () => import('../components/admin/CategoryList.vue') },
      {
        path: 'orders',
        component: () => import('../components/admin/OrderList.vue'),
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
          path: 'tintuc', 
           component: () => import('../components/admin/TinTuc.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: '/admin/slide',
        name: 'AdminSlide',
        component: () => import('../components/admin/SlideList.vue'),
        meta: { requiresAuth: true, role: 'admin' },
      },
      {
        path: '/admin/products/:id/variants',
        name: 'ProductVariants',
        component: () => import('../views/admin/sanphambt/ProductVariants.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: '/admin/products/:id/edit',
        component: () => import('../views/admin/EditProduct.vue')
      },
      {
        path: '/admin/products/add',
        component: () => import('../views/admin/AddProduct.vue'),
        meta: { requiresAuth: true, role: 'admin' }
      },
      {
      path: '/admin/danh-muc-tin-tuc/:id/edit',
      name: 'EditDmTinTuc',
      component: () => import('../views/admin/danhmuctt/Editdanhmuctt.vue'),
      meta: { requiresAuth: true, role: 'admin' }
      },
      {
      path: '/admin/danh-muc-tin-tuc/add',
      name: 'AddDmTinTuc',
      component: () => import('../views/admin/danhmuctt/Adddanhmuctt.vue'),
      meta: { requiresAuth: true, role: 'admin' }
      },
      {
      path: '/admin/tintuc/add',
      name: 'AddTintuc',
      component: () => import('../views/admin/Tintuc/Addtintuc.vue'),
      meta: { requiresAuth: true, role: 'admin' }
      },
      {
        path: '/admin/tintuc/:id/edit',
        name: 'EditTintuc',
        component: () => import('../views/admin/Tintuc/Edittintuc.vue'),
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
    path: '/admin/products/:id',
    name: 'product-detail',
    component: () => import('../views/admin/ProductDetail.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  
  {
    path: '/admin/categories/add',
    component: () => import('../views/admin/categories/AddCategories.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/categories/:category_id/products',
    name: 'CategoryProducts',
    component: () => import('../components/admin/CategoriesProduct.vue')
  },
  


{
  path: '/:pathMatch(.*)*',
  redirect: '/'
}
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
