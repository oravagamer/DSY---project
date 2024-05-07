import {Link, Outlet} from "react-router-dom";
import styles from "./Layout.module.scss";
import useAuthDataStore from "../store/authDataStore.js";

const Layout = () => {
    const auth = useAuthDataStore();
    return (<div className={styles["background"]}>
        <nav className={styles["nav-bar"]}>
            <div className={styles["nav-left"]}><a href="#" onClick={() => auth.logout()}><img src="logout.svg"
                                                                                               alt="Logout" /></a>
            </div>
            <div className={styles["nav-middle"]}><Link to="/dash"><img src="logo.svg" alt="Website logo" /></Link>
            </div>
            <div className={styles["nav-right"]}><Link to={`/dash/user/edit`}><img src="user.svg"
                                                                                   alt="Edit profile" /></Link>
            </div>
        </nav>
        <div className={styles["web-center"]}>
            <nav className={styles["left-nav"]}>
                <div><Link to="/dash">Orders</Link></div>
                <div><Link to="/dash/order/add">Upload</Link></div>
            </nav>
            <Outlet />
        </div>
        <footer className={styles["footer"]}>
            <div className={styles["copyright"]}>©</div>
            <div className={styles["santos"]}>Santos_Father</div>
        </footer>
    </div>)
}

export default Layout;