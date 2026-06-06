import { useEffect, useState } from "react";
import type { AccountRequest } from "../components/sharedComponents/interfaces/Admin";
import AdminTable from "../components/sharedComponents/AdminTable/AdminTable";
import "../components/sharedComponents/AdminTable/AdminTable.css";

export default function AdminDashboard() {
    const [requests, setRequests] = useState<AccountRequest[]>([]);
    const [popup, setPopup] = useState({ 
        show: false, 
        type: "success" as "success" | "error", 
        message: "" 
    });

    // fetch data
    const triggerRefresh = async () => {
        try {
            const token = localStorage.getItem("admin_token") || localStorage.getItem("token");

            const response = await fetch("http://127.0.0.1:8000/api/account/requests", {
                method: "GET",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                }
            });
            
            if (!response.ok) {
                throw new Error(`Server status: ${response.status}`);
            }

            const result = await response.json();
            // pass data 
            setRequests(Array.isArray(result.data) ? result.data : result); 
        } catch (error) {
            console.error("Refresh error", error);
        }
    };

    // fetch first data
    useEffect(() => {
        let isMounted = true;
        const loadInitialData = async () => {
            try {
                const token = localStorage.getItem("admin_token") || localStorage.getItem("token");

                const response = await fetch("http://127.0.0.1:8000/api/account/requests", {
                    method: "GET",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                        "Authorization": `Bearer ${token}`
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`Server status: ${response.status}`);
                }

                const result = await response.json();
                if (isMounted) {
                    
                    setRequests(Array.isArray(result.data) ? result.data : result);
                }
            } catch (error) {
                console.error("Fetch error", error);
            }
        };
        
        loadInitialData();
        return () => { isMounted = false; };
    }, []);

    // accept request
    const handleAccept = async (id: number) => {
        try {
            const token = localStorage.getItem("admin_token") || localStorage.getItem("token");

            const response = await fetch(`http://127.0.0.1:8000/api/account/${id}/accept`, {
                method: "POST",
                headers: { 
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                }
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || "Failed to accept the request.");
            }

            setPopup({ 
                show: true, 
                type: "success", 
                message: `${result.message || "Request approved successfully"}. Verification Code: ${result.data?.verification_code || 'N/A'}` 
            });
            triggerRefresh();

        } catch (error: unknown) {
            console.error(error);
            setPopup({ show: true, type: "error", message: (error as Error).message || "Connection error during acceptance." });
        }
    };

    // delet request
    const handleReject = async (id: number) => {
        try {
            const token = localStorage.getItem("admin_token") || localStorage.getItem("token");

            const response = await fetch(`http://127.0.0.1:8000/api/account/${id}/reject`, {
                method: "POST",
                headers: { 
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                }
            });

            const result = await response.json().catch(() => ({ message: "Server error occurred." }));

            if (response.status === 422) {
                setPopup({ show: true, type: "error", message: result.message });
            } else if (response.ok) {
                setPopup({ show: true, type: "success", message: result.message || "Request has been rejected successfully." });
                triggerRefresh();
            } else {
                throw new Error(result.message || "Failed to reject request.");
            }
        } catch (error: unknown) {
            console.error(error);
            setPopup({ show: true, type: "error", message: (error as Error).message || "Network error during rejection." });
        }
    };

    return (
        <div className="admin-dashboard-wrapper">
            <header className="admin-header">
                <h1>Bank Account Requests Management</h1>
                <p>Monitor and process incoming banking applications</p>
            </header>

            <AdminTable 
                requests={requests} 
                onAccept={handleAccept} 
                onReject={handleReject} 
            />

            {popup.show && (
                <div className="mh-popup-overlay">
                    <div className={`mh-popup-card ${popup.type}`}>
                        <div className="popup-icon">
                            {popup.type === "success" ? "✓" : "✕"}
                        </div>
                        <h3>{popup.type === "success" ? "Operation Successful" : "Action Stopped"}</h3>
                        <p>{popup.message}</p>
                        <button 
                            className="btn-close-popup"
                            onClick={() => setPopup(prev => ({ ...prev, show: false }))}
                        >
                            Confirm & Close
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}