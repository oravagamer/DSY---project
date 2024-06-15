import React, {useEffect, useState} from "react";
import useAuthDataStore from "../store/authDataStore.js";
import useFetch from "../hooks/useFetch.js";
import {backendUrl} from "../../settings.js";
import styles from "./UserSelect.module.scss";

const UsersSelect = React.forwardRef(({defaultUser}, ref) => {
    const [selectMode, setSelectMode] = useState(false);
    const [user, setUser] = useState();
    const auth = useAuthDataStore();
    const [{responseData, responseStatus, loading, error}, refetch] = useFetch(`${backendUrl}/users`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${auth.accessToken}`
        }
    });

    const setRef = data => {
        ref.current = {...data, ...ref.current};
        setUser(data);
    }

    useEffect(() => {
            if (defaultUser !== undefined) {
                fetch(`${backendUrl}/user?id=${defaultUser}`, {
                    method: "GET",
                    headers: {
                        "Authorization": `Bearer ${auth.accessToken}`
                    }
                })
                    .then(async res => {
                        setRef(await res.json());
                    })
            }
        },
        [defaultUser]
    );
    const selectUser = () => {
        refetch();
        setSelectMode(true);
    };

    const closeSelectUser = (input_user) => {
        setRef(input_user);

        setSelectMode(false);
    }

    useEffect(() => {

    }, [user]);
    return (<div>
        <input className={styles["user-select-button"]} type="button" onClick={selectUser}
               value={`Select user Selected user: ${ref.current?.username === undefined ? "None" : ref.current?.username}`} />
        {selectMode ?
            <div>
                <div className={styles["user-select-miss-click"]} onClick={() => closeSelectUser()} />
                <div className={styles["select-box"]}>
                    <table className="table">
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
                                    <tr className={styles["user-select-table-button"]} key={value.id}
                                        onClick={() => closeSelectUser(value)}>
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
});

export default UsersSelect;