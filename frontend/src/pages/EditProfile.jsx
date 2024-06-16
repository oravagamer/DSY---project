import Section from "../components/Section.jsx";
import GoBack from "../components/GoBack.jsx";
import {useNavigate, useParams} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import {useEffect, useRef} from "react";
import customFetch from "../functions/customFetch.js";
import styles from "./EditProfile.module.scss";

const EditProfile = () => {
    const {id} = useParams();
    const auth = useAuthDataStore();
    const usernameRef = useRef();
    const firstNameRef = useRef();
    const lastNameRef = useRef();
    const emailRef = useRef();
    const navigate = useNavigate();
    const [{responseData, loading}] = useFetch(`${backendUrl}/user?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    const saveChanges = () => {
        customFetch(`${backendUrl}/user?id=${id}`, {
            method: "PUT",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                username: usernameRef.current.value,
                first_name: firstNameRef.current.value,
                last_name: lastNameRef.current.value,
                email: emailRef.current.value
            })
        })
    }
    const deleteUser = () => {
        customFetch(`${backendUrl}/user?id=${id}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            }
        })
            .then(async res => {
                if (await res.response.status < 400) {
                    await navigate("/dash/home")
                }
            });
    }

    useEffect(() => {
        usernameRef.current.value = responseData?.username;
        firstNameRef.current.value = responseData?.first_name;
        lastNameRef.current.value = responseData?.last_name;
        emailRef.current.value = responseData?.email;
    }, [loading]);

    return (<Section className={styles["edit-profile"]}>
        <img className={styles["user-profil-picture-image---"]} src="/user.svg" />
        <input className={styles["input-user_name"]} type="text" ref={usernameRef} />
        <input className={styles["input-first_name"]} type="text" ref={firstNameRef} />
        <input className={styles["input-last_name"]} type="text" ref={lastNameRef} />
        <input className={styles["input-email"]} type="email" ref={emailRef} />
        <input className={styles["input-change_password"]} type="button" value="Change password" />
        <input className={styles["input-save_changes"]} type="button" value="Save changes" onClick={saveChanges} />
        <input className={styles["input-delete"]} type="button" value="Delete" onClick={deleteUser} />
        <GoBack className={styles["input-go_back"]} />
    </Section>)
}

export default EditProfile;