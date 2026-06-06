import { useState } from "react"; 
import Form from '../components/sharedComponents/formShared/Form';
import type { Field } from '../components/sharedComponents/interfaces/form';
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
export default function Login() {
    const navigate = useNavigate(); 
    const { login } = useAuth();
    //popUp
    const [popup, setPopup] = useState<
    { show: boolean; type: "success" | "error"; message: string }>({
        show: false,
        type: "success",
        message: ""
    });
    //array to log in
    const loginFields: Field[] = [
        { name: "email", type: "email", placeholder: "Enter your Email" },
        { name: "password", type: "password", placeholder: "Enter your Password" },
    ];
    //handleDta after submite
    const handleLoginSubmit = async (data: Record<string, string>) => {
        
        const formData = new FormData();
        formData.append("email", data.email);
        formData.append("password", data.password);

        try {
            const response = await fetch("http://127.0.0.1:8000/api/login", {
                method: "POST",
                body: formData, 
            });

            const result = await response.json();

            if (response.ok) {
                console.log("Successful LogIn ", result);
                login(result.user);
                // save Data in localStorage
                localStorage.setItem("token", result.token);
                

                setPopup({ show: true, type: "success", message: "Login Successful! Redirecting..." });

                setTimeout(() => {
                    setPopup(prev => ({ ...prev, show: false }));
                    
                    // validation if users or admine
                    if (result.user.role === "admin") {
                        navigate("/admin/dashboard"); 
                    } else {
                        navigate("/dashboard");
                    }
                }, 2000);
                
            } else {
                console.error("Login failed:", result.message);
                setPopup({ 
                    show: true, 
                    type: "error", 
                    message: result.message || "Invalid email or password." 
                });
            }
        } catch (error) {
            console.error("Network error:", error);
            setPopup({ show: true, type: "error", message: "Network error. Please check your backend server." });
        }
    };

    return (
        <>
            <Form
                title="Login"
                description="Welcome back! Please log in to access your account."
                fields={loginFields}
                submitText="Login"
                secondaryText="Sign Up"
                onSubmit={handleLoginSubmit} 
            />
            
            {/* Popup UI remains the same */}
            {popup.show && (
                <div className="mh-popup-overlay">
                    <div className={`mh-popup-card ${popup.type}`}>
                        <h3>{popup.type === "success" ? "Success!" : "Oops!"}</h3>
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