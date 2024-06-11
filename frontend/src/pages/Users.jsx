import Section from "../components/Section.jsx";
import {Link} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import styles from "./Users.module.scss";

const Users = () => {
    const auth = useAuthDataStore();
    const [{responseData}] = useFetch(`${backendUrl}/users.php`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });
    return (<Section className={styles["users-section"]}>
        <table className="table">
            <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>First name</th>
                <th>Last name</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            {responseData?.map(value => (<tr key={value.id}>
                <td>{value.username}</td>
                <td>{value.email}</td>
                <td>{value.first_name}</td>
                <td>{value.last_name}</td>
                <td><Link to={`${value.id}`}>Info</Link></td>
            </tr>))}
            </tbody>
        </table>
    </Section>)
}

export default Users;