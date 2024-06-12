import Section from "../components/Section.jsx";
import {Link, useParams} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import GoBack from "../components/GoBack.jsx";
import styles from "./Profile.module.scss";

const Profile = () => {
    const {id} = useParams();
    const auth = useAuthDataStore();
    const [{responseData}] = useFetch(`${backendUrl}/user.php?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });
    return (<Section className={styles["profile"]}>
        <h1 className={styles["user"]}>User: {responseData?.username}</h1>
        <h2 className={styles["first-name"]}>First name: {responseData?.first_name}</h2>
        <h2 className={styles["last-name"]}>Last name: {responseData?.last_name}</h2>
        <h2 className={styles["gaymail"]}>Email: {responseData?.email}</h2>
        <h3>
            <h2 className={styles["role"]}>Roles:</h2>
            <ul>{responseData && responseData.roles && responseData.roles.map && responseData.roles.map(value => <li
                key={value}>{value}</li>)}</ul>
        </h3>
        <GoBack className={styles["GoBack"]} />
        <Link className={styles["edit-style"]} to="edit">Edit</Link>
    </Section>)
}

export default Profile;