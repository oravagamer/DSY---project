import {
    Table, TableBody, TableCell, TableContainer, TableSortLabel, TableHead, TablePagination, TableRow, Button, Fab

} from '@mui/material';
import {Link, useParams} from "react-router-dom";
import useOravixFetch from "../hooks/useOravixFetch.js";
import {backendUrl} from "../../settings.js";
import useOravixSecurity from "../hooks/useOravixSecurity.js";
import AddIcon from '@mui/icons-material/Add';
import SelectRole from "../components/SelectRole.jsx";
import {useState} from "react";

const EditUserRoles = () => {
    const {id} = useParams();
    const {
        data, loading, refetch
    } = useOravixFetch(`${backendUrl}/user/roles/?id=${id}`, {method: "GET"}, true, true, []);
    const {security} = useOravixSecurity();
    const [rolesSelectOpen, setRolesSelectOpen] = useState(false);
    return (<><TableContainer sx={{width: "100%", flexGrow: 1, height: "100%"}}>
        <Table stickyHeader>
            <TableHead>
                <TableRow>
                    <TableCell>Name</TableCell>
                    <TableCell>Description</TableCell>
                    <TableCell>Level</TableCell>
                    <TableCell>Action</TableCell>
                </TableRow>
            </TableHead>
            <TableBody>
                {data?.map && data?.map(value => <TableRow key={value.name}>
                    <TableCell>{value.name}</TableCell>
                    <TableCell>{value.description}</TableCell>
                    <TableCell>{value.level}</TableCell>
                    <TableCell><Button color="error" sx={{width: "100%"}} variant="outlined" onClick={async () => {
                        await security.secureEncryptedFetch(`${backendUrl}/user/roles/?user_id=${id}&role_id=${value.id}`, {method: "DELETE"})
                        await refetch();
                    }}>Remove</Button></TableCell>
                </TableRow>)}
            </TableBody>
        </Table>
    </TableContainer>
        <Fab color="primary" aria-label="add" sx={{position: "absolute", bottom: "10px", right: "10px"}}
             onClick={() => setRolesSelectOpen(true)}>
            <AddIcon />
        </Fab>
        <SelectRole open={rolesSelectOpen} user={id} onSelect={async (role) => {
            setRolesSelectOpen(false);
            if (role) {
                await security.secureEncryptedFetch(`${backendUrl}/user/roles/?user_id=${id}&role_id=${role}`, {method: "POST"});
                await refetch()
            }
        }} />
    </>)
}

export default EditUserRoles;
