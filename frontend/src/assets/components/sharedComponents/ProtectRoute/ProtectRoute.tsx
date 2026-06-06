//this component is used to protect routes 
// that require authentication. It checks if the user is authenticated by looking for a token
// in localStorage. If the token is not found, it redirects the user to the login page. 
// If the token is found, it allows the user to access 
// the protected route. The AdminRoute component checks if the user has
//  an admin role and redirects accordingly, while the GuestRoute component 
// redirects authenticated users away from guest-only pages.
import { Navigate } from "react-router-dom";
//validate if is admine
export function ProtectedRoute({ children }: { children: React.ReactNode }) {
    const token = localStorage.getItem("token");
    if (!token) return <Navigate to="/login" replace />;
    return <>{children}</>;
}
//validate if user
export function AdminRoute({ children }: { children: React.ReactNode }) {
    const token = localStorage.getItem("token");
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    if (!token) return <Navigate to="/login" replace />;
    if (user.role !== "admin") return <Navigate to="/dashboard" replace />;
    return <>{children}</>;
}

export function GuestRoute({ children }: { children: React.ReactNode }) {
    const token = localStorage.getItem("token");
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    
    if (token && user.role === "admin") return <Navigate to="/admin/dashboard" replace />;
    if (token) return <Navigate to="/dashboard" replace />;
    return <>{children}</>;
}