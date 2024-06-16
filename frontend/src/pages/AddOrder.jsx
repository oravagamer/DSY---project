import Section from "../components/Section.jsx";
import styles from "./AddOrder.module.scss";
import {useEffect, useRef, useState} from "react";
import {backendUrl} from "../../settings.js";
import useAuthDataStore from "../store/authDataStore.js";
import UsersSelect from "../components/UsersSelect.jsx";
import customFetch from "../functions/customFetch.js";
import {redirect, useNavigate} from "react-router-dom";

const AddOrder = () => {
    const auth = useAuthDataStore();
    const nameRef = useRef();
    const finishDateRef = useRef();
    const descriptionRef = useRef();
    const [images, setImages] = useState([]);
    const imagesRef = useRef();
    const formRef = useRef();
    const userRef = useRef();
    const navigate = useNavigate();

    useEffect(() => {
    }, [images]);

    const onSubmit = () => {
        if (formRef.current.reportValidity()) {
            const formData = new FormData();
            if (nameRef.current?.value !== "") {
                formData.append("name", nameRef.current?.value);
            }
            if (descriptionRef.current?.value !== "") {
                formData.append("description", descriptionRef.current?.value);
            }
            if (finishDateRef.current?.value !== "") {
                formData.append("finish_date", (new Date(finishDateRef.current?.value).getTime() / 1000).toString());
            }
            if (userRef.current?.user !== undefined) {
                formData.append("created_for", userRef.current?.user.id);
            }
            for (const image of images) {
                formData.append("images[]", image);
            }
            customFetch(`${backendUrl}/order`, {
                method: "POST",
                body: formData,
                headers: {
                    "Authorization": `Bearer ${auth.accessToken}`
                }
            })
                .then(
                    async data => {
                        if ((await data.response.status) === 200) {
                            navigate(`/dash/home#${(await data.response.json()).order_id}`);
                        }
                    }
                )
        }
    }

    return (<Section className={styles["add-order"]}>
            <form className={styles["form"]} ref={formRef}>
                <input type="text" className={styles["name"]} ref={nameRef} required={true} placeholder="Name" />
                <input type="datetime-local" className={styles["time"]} ref={finishDateRef}
                       placeholder="Finish date" required={true} />
                <textarea className={styles["desc"]} ref={descriptionRef} placeholder="Description" required={true} />
                <UsersSelect ref={userRef} />
                <label htmlFor="file-upload" className={styles["file-label"]}>
                    <div className={styles["upload-button-container"]} onClick={event => {
                        imagesRef.current.value = null;
                        imagesRef.current.click();
                    }}>
                        <img className={styles["upload-button"]} src="/upload_foto.svg" alt="upload button" />
                        <div>Select Files</div>
                    </div>
                    <ul>
                        {images.map(
                            (value, index) => (
                                <li key={index}><img className={styles["upload-image"]} datatype={value.type}
                                                     src={URL.createObjectURL(value)} alt={value.name} />
                                    <div>{value.name}</div>
                                    <button className={styles["remove-image"]} type="button"
                                            onClick={() => setImages(prevState => prevState.filter((filterValue, filterIndex) => filterIndex !== index))}>Remove
                                    </button>
                                </li>)
                        )}
                    </ul>
                </label>
                <input type="file" name="file-upload" className={styles["file"]} ref={imagesRef}
                       accept="image/*" multiple={true}
                       onChange={event => {
                           event.preventDefault();
                           setImages(prevState => [...prevState, ...Array.from(event.target.files)]);
                       }}
                       hidden={true} />
                <input type="button" className={styles["button"]} value="Submit" onClick={onSubmit} />
            </form>
        </Section>
    )
}

export default AddOrder;
