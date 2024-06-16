import styles from "./Home.module.scss";
import Section from "../components/Section.jsx";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import useAuthDataStore from "../store/authDataStore.js";
import {Link} from "react-router-dom";
import {useEffect} from "react";

const Home = () => {
    const auth = useAuthDataStore();
    const [{responseData, responseStatus, loading, error}] = useFetch(`${backendUrl}/orders`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    useEffect(() => {
        try {
            document.getElementById(location.hash.replace("#", "")).scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        } catch (error) {

        }
    }, [loading]);

    return (<Section className={styles["home"]}>
        <table className="table">
            <thead>
            <tr>
                <th colSpan={2}>Job</th>
                <th>Date</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            {responseData?.map && responseData?.map(value => (<tr className={styles["tr"]} key={value.id} id={value.id}>
                <td className={styles[value.status === null ? "created" : value.status === 1 ? "in-progress" : "finished"] + " " + styles["status"]} />
                <td>{value.name}</td>
                <td className={styles["time"]}>{new Date(value.created_date).toUTCString()}</td>
                <td className={styles["time"]}>{new Date(value.finish_date).toUTCString()}</td>
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