import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import {Link, Navigate, useParams} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import Section from "../components/Section.jsx";
import GoBack from "../components/GoBack.jsx";

const Order = () => {
    const {id} = useParams();
    const auth = useAuthDataStore();
    const [{responseData}] = useFetch(`${backendUrl}/order.php?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    return (<Section>
            <h1>{responseData?.order.name}</h1>
            <h2>Description: {responseData?.order.description}</h2>
            <h3>Created: {responseData?.order.created_date}</h3>
            <h3>Finish: {responseData?.order.finish_date}</h3>
            <h3>Status: {responseData?.order.status === null ? "Created" : responseData?.order.status === 1 ? "In progress" : "Finished"}</h3>
            <ul>{responseData && responseData?.images?.map && responseData?.images?.map(value => <li key={value}><img
                src={`${backendUrl}/image.php?id=${value}`} alt={value} /></li>)}</ul>
            <GoBack />
            <Link to="edit">Edit</Link>
        </Section>
    )
}

export default Order;