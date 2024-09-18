import {useState, useEffect} from "react";
import {
    IconButton,
    FormControl,
    Input,
    InputAdornment,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableSortLabel,
    TableHead,
    TablePagination,
    TableRow,
    Divider,

} from '@mui/material';
import {Link} from "react-router-dom";
import useOravixFetch from "../hooks/useOravixFetch.js";
import {backendUrl} from "../../settings.js";

const EditUserRoles = () => {
  return (<TableContainer sx={{width: "100%", flexGrow: 1, height: "100%"}}>
        <Table stickyHeader>
            <TableHead>
                <TableRow>
                    <TableCell>Name</TableCell>
                    <TableCell>Description</TableCell>
                    <TableCell>Level</TableCell>
                </TableRow>
            </TableHead>
            <TableBody>
                {
                    data?.roles?.map && data?.roles.map(value => <TableRow key={value.id}>
                    <TableCell>{value.name}</TableCell>
                    <TableCell>{value.level}</TableCell>
                    <TableCell>{value.description}</TableCell>
                        {value.name === "admin" || value.name === "default" ? <TableCell /> : <TableCell><Link to={value.id}>Edit</Link></TableCell>}
                    </TableRow>)
                }
            </TableBody>
        </Table>
    </TableContainer>)
}

export default EditUserRoles;
