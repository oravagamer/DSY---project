import Section from "../components/Section.jsx";
import {Link, Navigate, useParams} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import {useEffect, useRef} from "react";
import GoBack from "../components/GoBack.jsx";

const EditOrder = () => {
    const {id} = useParams();
    const auth = useAuthDataStore();
    const nameRef = useRef();
    const descriptionRef = useRef();
    const createdForRef = useRef();
    const finishDateRef = useRef();
    const statusRef = useRef();
    const [{responseData, responseStatus, loading, error}] = useFetch(`${backendUrl}/order.php?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    const saveChanges = () => {
        fetch(`${backendUrl}/order.php?id=${id}`, {
            method: "PUT",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            },
            body: JSON.stringify({
                name: nameRef.current.value,
                description: descriptionRef.current.value,
                finish_date: new Date(finishDateRef.current.value).getTime() / 1000,
                created_for: null, // forRef.current.value,
                status: statusRef.current.value === "0" ? null : parseInt(statusRef.current.value)
            })
        })
    }

    const deleteOrder = () => {
        fetch(`${backendUrl}/order.php?id=${id}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            }
        })
            .then(async res => {
                if (await res.status < 400) {
                    await location.replace("/dash/home")
                }
            });
    }

    useEffect(() => {
        nameRef.current.value = responseData?.order.name;
        descriptionRef.current.value = responseData?.order.description;
        createdForRef.current.value = responseData?.order.created_for;
        finishDateRef.current.value = responseData?.order.finish_date;
        statusRef.current.value = responseData?.order.status === null ? 0 : responseData?.order.status;
    }, [loading]);

    return (<>{responseStatus === 404
        ? <Navigate to="/dash/home" />
        : <Section>
            <input type="text" ref={nameRef} />
            <input type="text" ref={descriptionRef} />
            <input type="text" ref={createdForRef} />
            <input type="datetime-local" ref={finishDateRef} />
            <select name="Status" defaultChecked={true} ref={statusRef}>
                <option value={0}>Created</option>
                <option value={1}>In progress</option>
                <option value={2}>Finished</option>
            </select>
            <input type="button" value="Save Changes" onClick={saveChanges} />
            <input type="button" value="Delete" onClick={deleteOrder} />
            <GoBack/>
        </Section>
    }</>)
}

export default EditOrder;