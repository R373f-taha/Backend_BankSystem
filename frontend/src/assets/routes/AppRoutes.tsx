import { createBrowserRouter } from 'react-router-dom';
import Home from '../pages/Home';
import LogIn from '../pages/LogIn';
import RootLayout from '../components/sharedComponents/layOut/RootLayout';
import Dashboard from '../pages/Dashboard';
import CreatAccount from "../pages/CreatAccount"; 
import AdminDashboard from '../pages/AdminDashboard';
import VerifyAccount from '../pages/VerifyAccount';
import Register from '../pages/Register';
import { ProtectedRoute, AdminRoute, GuestRoute } from '../components/sharedComponents/ProtectRoute/ProtectRoute';

export const router = createBrowserRouter([{
    path:'/',
    element:<RootLayout/>,
    children:[
        {
            path:"/",
            element:<Home/>
        },
        {
            path:'login',
            element:<GuestRoute><LogIn/></GuestRoute>
        },
        {
            path: 'register',
            element: <GuestRoute><Register /></GuestRoute>
        },
        {
            path:'signup',
            element:<GuestRoute><CreatAccount /></GuestRoute>
        },
        {
            path:'dashboard',   
            element:<ProtectedRoute><Dashboard /></ProtectedRoute>
        },
        {
            path: 'admin/dashboard',
            element: <AdminRoute><AdminDashboard /></AdminRoute>
        },
        {
            path:"verify-account",
            element:<VerifyAccount/>
        }
    ]
}])