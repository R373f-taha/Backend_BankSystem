import { NavLink } from 'react-router-dom';
import './Footer.css';
import type { FooterProps, FooterNavItem, ContactItem, SocialIcon } from '../interfaces/footer.ts';

const Footer = ({ logo, items, contact, icons, rights, p, s }: FooterProps) => {
    return (
        <footer className='flex-center flex-col'>
            
            <div className="js_logo_url_cnt flex-center flex-col">
                <img src={logo} className="js_logo" alt="Footer Logo" />
                <ul>
                    {items.map((item: FooterNavItem, index: number) => (
                        <li key={index}>
                            <NavLink to={item.href}>
                                {item.content}
                            </NavLink>
                        </li>
                    ))}
                </ul>
            </div>

            
            <div className="js_mail_num_loc_cnt flex-center">
                {contact.map((item: ContactItem, index: number) => (
                    <div className='js_pic_text_align' key={index}>
                        <img src={item.icon} className='js-footer-icons' alt="contact icon" />
                        <p>{item.content}</p>
                    </div>
                ))}
            </div>

            
            <div className="js_sm_rps_cnt flex-between border">
                
                <div className="js-sm-cnt flex-center">
                    {icons.map((icon: SocialIcon, index: number) => (
                        <NavLink to={icon.href} key={index} className="js-sm-yellow-cont">
                            <img src={icon.content} alt="social icon" />
                        </NavLink>
                    ))}
                </div>

                <div className="js-rights-cnt">
                    <p>{rights}</p>
                </div>

                <div className="js-pr-ser-cnt">
                    <p>{p}</p>
                    <p className='js-service-p'>{s}</p>
                </div>
            </div>
        </footer>
    );
}

export default Footer;