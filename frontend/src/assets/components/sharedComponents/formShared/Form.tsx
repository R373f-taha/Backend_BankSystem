import { useState } from "react";
import { useNavigate } from "react-router-dom";
import "./Form.css";
import type { Field, SharedFormProps } from '../interfaces/form.ts';

// import photo
import googleIcon from "../../../images/logInSignUpImages/Google.png";
import facebookIcon from "../../../images/logInSignUpImages/Facebook.png";
import appleIcon from "../../../images/logInSignUpImages/Apple.png";
import mainTexture from "../../../images/logInSignUpImages/Main Texture.png";
import showPasswordIcon from "../../../images/logInSignUpImages/Show Password.png";

const SOCIAL_ICONS = [
    { id: 1, src: googleIcon, alt: "Google", url: "https://www.google.com/" },
    { id: 2, src: facebookIcon, alt: "Facebook", url: "https://www.facebook.com/" },
    { id: 3, src: appleIcon, alt: "Apple", url: "https://www.apple.com/" },
];

export default function Form(
    { title, description, fields, submitText, secondaryText, onSubmit }: SharedFormProps)
    {
        
    const [showPassword, setShowPassword] = useState<boolean>(false);
    const [formData, setFormData] = useState<Record<string, string>>({}); 
    const navigate = useNavigate();
    //function to handleChanges in form
    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };
    //fetch data after Submit
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSubmit(formData); 
    };
    //choose which page 
    const handleSecondaryClick = (): void => {
        if (secondaryText === "Sign Up") navigate("/create-account");
        else if (secondaryText === "Login") navigate("/login");
    };

    return (
        <section className="mh-login-page">
            <div className="image-container">
                <img src={mainTexture} className="mh-mainTexture" alt="" />
            </div>

            <form className="mh-login-card" onSubmit={handleSubmit}>
                <h1 className="mh-title">{title}</h1>
                <p className="mh-desc">{description}</p>

                <div className="mh-auth-inputs">
                    {fields.map((field: Field) => {
                        // field for password
                        if (field.type === "password") {
                            return (
                                <div key={field.name} className="mh-password-wrapper">
                                    <input
                                        type={showPassword ? "text" : "password"}
                                        placeholder={field.placeholder}
                                        name={field.name}
                                        value={formData[field.name] || ""}
                                        onChange={handleChange} 
                                        required
                                    />
                                    <img
                                        src={showPasswordIcon} 
                                        alt="toggle password"
                                        className="mh-password-eye"
                                        onClick={() => setShowPassword(prev => !prev)}
                                    />
                                </div>
                            );
                        }

                        // field to choose
                        if (field.type === "select") {
                            return (
                                <select
                                    key={field.name}
                                    name={field.name}
                                    value={formData[field.name] || ""}
                                    onChange={handleChange}
                                    className="mh-form-select"
                                    required
                                >
                                    <option value="" disabled hidden>{field.placeholder}</option>
                                    {field.options?.map((opt) => (
                                        <option key={opt.value} value={opt.value}>
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                            );
                        }

                        // field for inputs
                        return (
                            <input
                                key={field.name}
                                type={field.type}
                                placeholder={field.placeholder}
                                name={field.name}
                                value={formData[field.name] || ""}
                                onChange={handleChange} 
                                required
                            />
                        );
                    })}
                </div>

                {title === "Login" && <a className="mh-forgot" href="#">Forgot Password?</a>}

                <div className="mh-loginOrSignup">
                    <input className="mh-btn-login" type="submit" value={submitText} />
                    <button className="mh-btn-signup" type="button" onClick={handleSecondaryClick}>
                        {secondaryText}
                    </button>
                </div>

                <div className="mh-divider"><span></span><p>Or Continue with</p><span></span></div>
                <div className="mh-social">
                    {SOCIAL_ICONS.map(icon => (
                        <span key={icon.id} className="mh-social-icon">
                            <a href={icon.url}><img src={icon.src} alt={icon.alt} /></a>
                        </span>
                    ))}
                </div>
            </form>
        </section>
    );
}