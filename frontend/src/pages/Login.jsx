import {useEffect, useRef, useState} from "react";
import styles from './Login.module.scss';
import useAuthDataStore from "../store/authDataStore.js";
import {frontendUrl} from "../../settings.js";

const Login = () => {
    const username = useRef("");
    const password = useRef("");
    const auth = useAuthDataStore();

    useEffect(() => {
    }, []);

    const login = async () => {
        auth.login(username.current.value, password.current.value);
    }

    return (<div className={styles["login-background"]}>
        <header className={styles["login-header"]}>
            <div><img src="/logo.svg" alt="Website logo" /></div>
        </header>
        <section className={styles["login-section"]}>
            <form className={styles["login-form"]}>
                <div className={styles["login-label"]}>Login</div>
                <input className={styles["login-username"]} type="text" placeholder="Username" ref={username} />
                <input className={styles["login-password"]} type="password" placeholder="Password" ref={password} />
                <input className={styles["login-button"]} type="button" value="Submit" onClick={login} />
            </form>
        </section>
    </div>)
}

export default Login;