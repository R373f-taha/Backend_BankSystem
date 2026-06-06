// src/pages/Dashboard.tsx
import { useEffect, useState, useCallback } from "react";
import { DashboardHeader } from "../components/DashboardComponent/DashboardHeader/DashboardHeader";
import { DashboardNotification } from "../components/DashboardComponent/DashboardNotifocations/DashboardNotification";
import type { Notification } from "../components/sharedComponents/interfaces/Notification";

export default function Dashboard() {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [unreadCount, setUnreadCount] = useState<number>(0);
    const [loading, setLoading] = useState<boolean>(true);
    // control visibility of notifications dropdown
    const [showNotifications, setShowNotifications] = useState(false); 
    
    const token = localStorage.getItem("token");
    // Fetch notifications from the API
    const fetchNotifications = useCallback(async () => {
        try {
            const response = await fetch("http://127.0.0.1:8000/api/notifications/all", {
                headers: { "Accept": "application/json", "Authorization": `Bearer ${token}` }
            });
            const result = await response.json();
            setNotifications(result.notifications || []);
            setUnreadCount(result.unread_count || 0);
        } catch (error) {
            console.error("Failed to fetch notifications", error);
        } finally {
            setLoading(false);
        }
    }, [token]);
    // Mark a single notification as read
    const handleMarkAsRead = async (id: number) => {
        await fetch(`http://127.0.0.1:8000/api/notifications/${id}/read`, {
            method: "POST",
            headers: { "Accept": "application/json", "Authorization": `Bearer ${token}` }
        });
        fetchNotifications();
    };
    // Mark all notifications as read
    const handleMarkAllAsRead = async () => {
        await fetch("http://127.0.0.1:8000/api/notifications/read-all", {
            method: "POST",
            headers: { "Accept": "application/json", "Authorization": `Bearer ${token}` }
        });
        fetchNotifications();
    };

    // delete a notification using FormData to comply with Laravel's expectations
    const handleDelete = async (id: number) => {
    // get token from localStorage to include in the request headers for authentication
    const token = localStorage.getItem("token");

    try {
        const response = await fetch(`http://127.0.0.1:8000/api/notifications/${id}`, {
            method: "DELETE", 
            headers: { 
                "Authorization": `Bearer ${token}`,
                
                "Accept": "application/json"
            }
        });

        if (!response.ok) throw new Error("Failed to delete");
        
        fetchNotifications();
    } catch (error) {
        console.error("Error deleting:", error);
    }
};
     //eslint-disable react-hooks/exhaustive-deps 
    useEffect(() => {
        fetchNotifications();
    }, [fetchNotifications]);

    return (
        <div className="dashboard-wrapper">
            
            <DashboardHeader 
                onToggleNotifications={() => setShowNotifications(!showNotifications)} 
                unreadCount={unreadCount}
            />
            
            
            {showNotifications && (
                <DashboardNotification 
                    notifications={notifications}
                    unreadCount={unreadCount}
                    loading={loading}
                    onMarkAsRead={handleMarkAsRead}
                    onMarkAllAsRead={handleMarkAllAsRead}
                    onDelete={handleDelete}
                />
            )}
        </div>
    );
}