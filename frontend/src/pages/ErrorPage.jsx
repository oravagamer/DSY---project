import Section from "../components/Section.jsx";
import GoBack from "../components/GoBack.jsx";
import {useRouteError, Link} from "react-router-dom";

const ErrorPage = () => {
    const error = useRouteError();
    return (<Section>
        <h1>Oops!</h1>
        <h2>{error.status}</h2>
        <p>{error.statusText}</p>
        {error.data?.message && <p>{error.data.message}</p>}
        <GoBack />
        <Link to="/login">Home</Link>
    </Section>)
}

export default ErrorPage;