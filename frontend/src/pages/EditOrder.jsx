import Section from "../components/Section.jsx";
import {Link, Navigate, useLoaderData, useParams} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import {useEffect, useRef, useState} from "react";
import GoBack from "../components/GoBack.jsx";
import styles from "./EditOrder.module.scss";
import UsersSelect from "../components/UsersSelect.jsx";
import customFetch from "../functions/customFetch.js";

const EditOrder = () => {
    const {id} = useParams();
    const auth = useAuthDataStore();
    const nameRef = useRef();
    const descriptionRef = useRef();
    const finishDateRef = useRef();
    const statusRef = useRef();
    const [user, setUser] = useState();
    const data = useLoaderData();
    const [{responseData, loading}] = useFetch(`${backendUrl}/order.php?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    const addImage = event => {
        event.stopPropagation();
        event.preventDefault();
        const file = event.target.files[0];
        customFetch(`${backendUrl}/image.php?id=${id}&type=${file.name.substring(file.name.lastIndexOf(".") + 1, file.name.size)}`, {
            method: "POST",
            body: file,
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            }
        })
            .then(async res => {
                event.target.value = null;
                refetch();
            });
    }

    const removeImage = event => {
        customFetch(`${backendUrl}/image.php?id=${event.target.id}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            }
        })
            .then(async res => {
                refetch();
            });
    }

    const saveChanges = () => {
        customFetch(`${backendUrl}/order.php?id=${id}`, {
            method: "PUT",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            },
            body: JSON.stringify({
                name: nameRef.current.value,
                description: descriptionRef.current.value,
                finish_date: new Date(finishDateRef.current.value).getTime() / 1000,
                created_for: user === undefined ? null : user.id,
                status: statusRef.current.value === "0" ? null : parseInt(statusRef.current.value)
            })
        })
    }

    const deleteOrder = () => {
        customFetch(`${backendUrl}/order.php?id=${id}`, {
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
        finishDateRef.current.value = responseData?.order.finish_date;
        statusRef.current.value = responseData?.order.status === null ? 0 : responseData?.order.status;
    }, [loading]);

    return (<Section>
        <input type="text" ref={nameRef} />
        <input type="text" ref={descriptionRef} />
        <UsersSelect defaultUser={responseData?.order.created_for} selectUser={setUser} />
        <input type="datetime-local" ref={finishDateRef} />
        <select name="Status" defaultChecked={true} ref={statusRef}>
            <option value={0}>Created</option>
            <option value={1}>In progress</option>
            <option value={2}>Finished</option>
        </select>
        <input type="button" value="Save Changes" onClick={saveChanges} />
        <label htmlFor="image-upload" className={styles["image-upload-button"]}>Add image</label>
        <input type="file" onChange={addImage} id={"image-upload"} hidden={true} />
        <input type="button" value="Delete" onClick={deleteOrder} />
        <GoBack />
        <ul>{responseData && responseData?.images?.map && responseData?.images?.map(value => <li key={value}><img
            src={`${backendUrl}/image.php?id=${value}`} alt={value} className={styles["images"]} />
            <button id={value} onClick={removeImage}>Remove</button>
        </li>)}</ul>
    </Section>)
}

export default EditOrder;