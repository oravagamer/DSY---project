import Section from "../components/Section.jsx";
import GoBack from "../components/GoBack.jsx";
import {useNavigate, useParams} from "react-router-dom";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import {useEffect, useRef} from "react";
import customFetch from "../functions/customFetch.js";

const EditProfile = () => {
    const {id} = useParams();
    const usernameRef = useRef();
    const firstNameRef = useRef();
    const lastNameRef = useRef();
    const emailRef = useRef();
    const navigate = useNavigate();
    const [{data, loading}] = useFetch(`${backendUrl}/user?id=${id}`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer`
        }
    });

    const saveChanges = () => {
        customFetch(`${backendUrl}/user?id=${id}`, {
            method: "PUT",
            headers: {
                "Authorization": `Bearer `,
                "Content-Type": "application/json"
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
        customFetch(`${backendUrl}/user?id=${id}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer `
            }
        })
            .then(async res => {
                if (await res.response.status < 400) {
                    await navigate("/dash/home")
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
        <img src="/user.svg" />
        <input type="text" ref={usernameRef} />
        <input type="text" ref={firstNameRef} />
        <input type="text" ref={lastNameRef} />
        <input type="email" ref={emailRef} />
        <input type="button" value="Change password" />
        <input type="button" value="Save changes" onClick={saveChanges} />
        <input type="button" value="Delete" onClick={deleteUser} />
        <GoBack />
    </Section>)
}

export default EditProfile;