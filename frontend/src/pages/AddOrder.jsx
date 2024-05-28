import Section from "../components/Section.jsx";
import styles from "./AddOrder.module.scss";
import {useRef, useState} from "react";
import {backendUrl} from "../../settings.js";
import useAuthDataStore from "../store/authDataStore.js";
import UsersSelect from "../components/UsersSelect.jsx";
import customFetch from "../functions/customFetch.js";

const AddOrder = () => {
    const auth = useAuthDataStore();
    const nameRef = useRef();
    const finishDateRef = useRef();
    const descriptionRef = useRef();
    const imagesRef = useRef();
    const [user, setUser] = useState();

    const onSubmit = () => {
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
        formData.append("created_for", user === undefined ? null : user.id);
        for (const image of imagesRef.current?.files) {
            formData.append("images[]", image);
        }
        customFetch(`${backendUrl}/order.php`, {
            method: "POST",
            body: formData,
            headers: {
                "Authorization": `Bearer ${auth.accessToken}`
            }
        })
    }

    return (<Section className={styles["add-order"]}>
            <form className={styles["add-order-form"]}>
                <input type="text" className={styles["add-order-name"]} size={256} ref={nameRef} placeholder="Name"/>
                <input type="datetime-local" className={styles["add-order-time"]} ref={finishDateRef}
                       placeholder="Finish date"/>
                <textarea className={styles["add-order-desc"]} ref={descriptionRef} placeholder="Description"/>
                <UsersSelect selectUser={setUser}/>
                <input type="file" name="file-upload" className={styles["add-order-file"]} ref={imagesRef} accept="image/*" multiple={true}/>
                <input type="button" className={styles["add-order-button"]} value="Submit" onClick={onSubmit}/>
            </form>
        </Section>
    )
}

export default AddOrder;