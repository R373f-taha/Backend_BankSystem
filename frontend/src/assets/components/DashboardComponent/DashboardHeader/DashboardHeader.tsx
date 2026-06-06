import { FaBell } from "react-icons/fa";
import "./DashboardHeader.css";

interface HeaderProps {
    onToggleNotifications: () => void;
    unreadCount: number;
}

export const DashboardHeader = ({ onToggleNotifications, unreadCount }: HeaderProps) => (
    <header className="dashboard-header">
        <h1>Welcome to Your Dashboard</h1>
        
        
        <div className="notification-bell" onClick={onToggleNotifications} style={{ cursor: 'pointer' }}>
            <FaBell size={30} />
            {unreadCount > 0 && (
                <span className="bell-badge">{unreadCount}</span>
            )}
        </div>
    </header>
);