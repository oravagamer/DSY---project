import {useEffect, useState} from "react";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import styles from "./UserSelect.module.scss";

const UsersSelect = props => {
    const [user, setUser] = useState();
    const [selectMode, setSelectMode] = useState(false);
    const auth = useAuthDataStore();
    const [{responseData, responseStatus, loading, error}, refetch] = useFetch(`${backendUrl}/users.php`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    useEffect(() => {
            fetch(`${backendUrl}/user.php?id=${props.defaultUser}`, {
                method: "GET",
                headers: {
                    "Authorization": `Bearer ${auth.accessToken}`
                }
            })
                .then(async res => setUser(await res.json()))
        },
        [props.defaultUser]
    );
    const selectUser = () => {
        refetch();
        setSelectMode(true);
    };

    const closeSelectUser = (input_user) => {
        setUser(input_user);
        props.selectUser(input_user);
        setSelectMode(false);
    }

    useEffect(() => {

    }, [user, selectMode]);
    return (<div>
        <input className={styles["user-select-button"]} type="button" onClick={selectUser} value={`Select user Selected user: ${user?.username === undefined ? "None" : user?.username}`} />
        {selectMode ?
            <div>
                <div className={styles["user-select-miss-click"]} onClick={() => closeSelectUser()} />
                <div className={styles["select-box"]}>
                    <table>
                        <thead>
                        <tr>
                            <th>Username</th>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>Email</th>
                        </tr>
                        </thead>
                        <tbody>
                        {
                            responseData && responseData.map && responseData.map(
                                value =>
                                    <tr className={styles["user-select-table-button"]} key={value.id} onClick={() => closeSelectUser(value)}>
                                        <td>{value.username}</td>
                                        <td>{value.first_name}</td>
                                        <td>{value.last_name}</td>
                                        <td>{value.email}</td>
                                    </tr>
                            )
                        }
                        </tbody>
                    </table>
                </div>
            </div>
            : null}
    </div>);
}

export default UsersSelect;