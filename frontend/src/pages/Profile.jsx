import Section from "../components/Section.jsx";
import {Link, useParams} from "react-router-dom";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import GoBack from "../components/GoBack.jsx";

const Profile = () => {
    const {id} = useParams();
    const [{data}] = useFetch(`${backendUrl}/user?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer `
        }
    });
    return (<Section>
        <h1>User: {responseData?.username}</h1>
        <h2>First name: {responseData?.first_name}</h2>
        <h2>Last name: {responseData?.last_name}</h2>
        <h2>Email: {responseData?.email}</h2>
        <h3>
            <h2>Roles:</h2>
            <ul>{responseData && responseData.roles && responseData.roles.map && responseData.roles.map(value => <li
                key={value}>{value}</li>)}</ul>
        </h3>
        <GoBack />
        <Link to="edit">Edit</Link>
    </Section>)
}

export default Profile;