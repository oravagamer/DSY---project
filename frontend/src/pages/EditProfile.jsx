import Section from "../components/Section.jsx";
import GoBack from "../components/GoBack.jsx";
import {useParams} from "react-router-dom";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import {useEffect, useRef} from "react";

const EditProfile = () => {
    const {id} = useParams();
    const auth = useAuthDataStore();
    const usernameRef = useRef();
    const firstNameRef = useRef();
    const lastNameRef = useRef();
    const emailRef = useRef();
    const [{responseData, responseStatus, loading, error}] = useFetch(`${backendUrl}/user.php?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    const saveChanges = () => {
        fetch(`${backendUrl}/user.php?id=${id}`, {
            method: "PUT",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            },
            body: JSON.stringify({
                username: usernameRef.current.value,
                first_name: firstNameRef.current.value,
                last_name: lastNameRef.current.value,
                email: emailRef.current.value
            })
        })
    }
    const deleteUser = () => {
        fetch(`${backendUrl}/user.php?id=${id}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            }
        })
            .then(async res => {
                if (await res.status < 400) {
                    await location.assign("/dash/home")
                }
            });
    }

    useEffect(() => {
        usernameRef.current.value = responseData?.username;
        firstNameRef.current.value = responseData?.first_name;
        lastNameRef.current.value = responseData?.last_name;
        emailRef.current.value = responseData?.email;
    }, [loading]);

    return (<Section>
        <input type="text" ref={usernameRef} />
        <input type="text" ref={firstNameRef} />
        <input type="text" ref={lastNameRef}/>
        <input type="email" ref={emailRef}/>
        <input type="button" value="Change password" />
        <input type="button" value="Save changes" onClick={saveChanges} />
        <input type="button" value="Delete" onClick={deleteUser}/>
        <GoBack />
    </Section>)
}

export default EditProfile;