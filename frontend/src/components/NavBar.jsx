import {Link, Outlet} from "react-router-dom";
import styles from "./NavBar.module.scss";
import useAuthDataStore from "../store/authDataStore.js";

const NavBar = () => {
    const auth = useAuthDataStore();
    return (<div className={styles["background"]}>
        <nav className={styles["nav-bar"]}>
            <div />
            <Link to="/dash" className={styles["nav-logo-link"]}><img src="logo.svg" alt="Website logo" /></Link>
            <Link to={`/dash/user/${auth.getJSONData().accessToken.payload.aud}/edit`}
                  className={styles["nav-logo-link"]}><img src="user.svg" alt="Edit profile" /></Link>
        </nav>
        <Outlet />
        <footer className={styles["footer"]}>
                <div className={styles["copyright"]}>©</div>
                <div className={styles["santos"]}>Santos_Father</div>
        </footer>
    </div>)
}

export default NavBar;