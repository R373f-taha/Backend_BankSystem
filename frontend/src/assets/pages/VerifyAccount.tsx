import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import Form from "../components/sharedComponents/formShared/Form";
import type { Field } from "../components/sharedComponents/interfaces/form";

export default function VerifyAccount() {
    const navigate = useNavigate();
    const location = useLocation();

    // fetch email 
    const incomingEmail = location.state?.email || "";

    const [popup, setPopup] = useState<{ show: boolean; type: "success" | "error" | "info"; message: string }>({
        show: false,
        type: "info",
        message: ""
    });

    // field of form
    const verifyFields: Field[] = [
        { 
            name: "email", 
            type: "email", 
            placeholder: "Enter your Email Address",
        },
        { 
            name: "verification_code", 
            type: "text", 
            placeholder: "Enter 8-Character Code"
        }
    ];
    //handle data
    const handleVerifySubmit = async (data: Record<string, string>) => {
        setPopup({ show: true, type: "info", message: "Checking verification status..." });

        try {
            // use email come from state
            const targetEmail = data.email && data.email.trim() !== "" ? data.email : incomingEmail;

            const response = await fetch("http://127.0.0.1:8000/api/verify-account", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify({ 
                    email: targetEmail, 
                    verification_code: data.verification_code 
                }),
            });

            const result = await response.json();

            if (response.ok) {
                setPopup({ 
                    show: true, 
                    type: "success", 
                    message: result.message || "Your account is active! Redirecting to login..." 
                });
                setTimeout(() => navigate("/register", { state: { email: targetEmail } }), 3000)
            } else {
                setPopup({ 
                    show: true, 
                    type: "error", 
                    message: result.message || "Verification failed. Please check your code." 
                });
            }
        } catch (error) {
            console.error("Verification connection error:", error);
            setPopup({ 
                show: true, 
                type: "error", 
                message: "Network error. Please check your backend server." 
            });
        }
    };

    return (
        <>
            
            <Form
                title="Verify Account" 
                description="Enter your registered email and the 8-character verification code sent by the admin."
                fields={verifyFields}
                submitText="Verify Account"
                secondaryText="Login"
                onSubmit={handleVerifySubmit}
            />

            
            {popup.show && (
                <div className="mh-popup-overlay">
                    <div className={`mh-popup-card ${popup.type}`}>
                        <h3>
                            {popup.type === "success" ? "Success!" : popup.type === "info" ? "Processing..." : "Oops!"}
                        </h3>
                        <p>{popup.message}</p>
                        
                        {popup.type === "error" && (
                            <button className="mh-popup-close-btn" onClick={() => setPopup(prev => ({ ...prev, show: false }))}>
                                Close
                            </button>
                        )}
                    </div>
                </div>
            )}
        </>
    );
}