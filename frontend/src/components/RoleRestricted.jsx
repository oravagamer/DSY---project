import useOravixFetch from "../hooks/useOravixFetch.js";
import {backendUrl} from "../../settings.js";

const RoleRestricted = ({children, role = "default"}) => {
    const {data, loading} = useOravixFetch(`${backendUrl}/role`, {method: "GET"}, true, true);

    return (loading
        ? ""
        : (Number.isInteger(role)
            ? (data.find(value => value.level === role) === undefined ? "" : children) : (data.find(value => value.name === role) === undefined ? "" : children)));
}

export default RoleRestricted;