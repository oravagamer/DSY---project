import styles from "./Home.module.scss";
import Section from "../components/Section.jsx";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import useAuthDataStore from "../store/authDataStore.js";
import {Link} from "react-router-dom";

const Home = () => {
    const auth = useAuthDataStore();
    const [{responseData, responseStatus, loading, error}] = useFetch(`${backendUrl}/orders.php`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    return (<Section className={styles["home-section"]}>
        <table className={styles["table"]}>
            <thead className={styles["table-head"]}>
            <tr>
                <th>Job</th>
                <th>Date</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            {responseData?.map(value => (<tr key={value.id}>
                <td>{value.name}</td>
                <td>{new Date(value.created_date).toUTCString()}</td>
                <td>{new Date(value.finish_date).toUTCString()}</td>
                <td>{(() => {
                    switch (value.status) {
                        case null:
                            return "Created";
                        case 1:
                            return "In Progress";
                        case 2:
                            return "Finished";
                    }
                })()}</td>
                <td><Link to={`/dash/order/${value.id}`}>Info</Link></td>
            </tr>))}
            </tbody>
        </table>
    </Section>)
}

export default Home;