import type {  AdminTableProps } from "../interfaces/Admin";
import "./AdminTable.css";



export default function AdminTable({ requests, onAccept, onReject }: AdminTableProps) {
    return (
        <div className="table-container">
            <table className="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {requests.length > 0 ? (
                        requests.map((req) => (
                            <tr key={req.id}>
                                <td>{req.id}</td>
                                <td>{req.full_name}</td>
                                <td>{req.email}</td>
                                <td>${req.deposit_amount}</td>
                                <td>
                                    <span className={`badge ${req.status}`}>
                                        {req.status}
                                    </span>
                                </td>
                                <td className="actions-cell">
                                    <button 
                                        className="btn-accept" 
                                        onClick={() => onAccept(req.id)}
                                        disabled={req.status !== "pending"}
                                    >
                                        Accept
                                    </button>
                                    <button 
                                        className="btn-reject" 
                                        onClick={() => onReject(req.id)}
                                        disabled={req.status === "rejected"}
                                    >
                                        Reject
                                    </button>
                                </td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td colSpan={6} style={{ textAlign: "center", padding: "20px" }}>
                                No requests found.
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
}