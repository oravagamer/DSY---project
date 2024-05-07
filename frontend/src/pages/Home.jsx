import styles from "./Home.module.scss";
import Section from "../components/Section.jsx";
import {useEffect, useState} from "react";

const vals = [
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    },
    {
        name: "aaa",
        created: 158022,
        finish: 5248058
    }
];

const Home = () => {
    const [orders, setOrders] = useState([]);

    useEffect(() => {
        setOrders(vals);
    }, [orders]);

    return (<Section className={styles["home-section"]}>
        <table className={styles["table"]}>
            <thead className={styles["table-head"]}>
            <tr>
                <th>Job</th>
                <th>Date</th>
                <th>Deadline</th>
            </tr>
            </thead>
            <tbody>
            {orders.map(value => (<tr>
                <td>{value.name}</td>
                <td>{new Date(value.created).toUTCString()}</td>
                <td>{new Date(value.finish).toUTCString()}</td>
            </tr>))}
            </tbody>
        </table>
    </Section>)
}

export default Home;