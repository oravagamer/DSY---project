import Section from "../components/Section.jsx";
import {Link, useParams} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import GoBack from "../components/GoBack.jsx";

const Profile = () => {
    const {id} = useParams();
    const auth = useAuthDataStore();
    const [{responseData, responseStatus, loading, error}] = useFetch(`${backendUrl}/user.php?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });
    return (<Section>
        <h1>{responseData?.username}</h1>
        <h2>{responseData?.first_name}</h2>
        <h2>{responseData?.last_name}</h2>
        <h2>{responseData?.email}</h2>
        <ul>{responseData && responseData.roles && responseData.roles.map && responseData.roles.map(value => <li>{value}</li>)}</ul>
        <GoBack />
        <Link to="edit">Edit</Link>
    </Section>)
}

export default Profile;