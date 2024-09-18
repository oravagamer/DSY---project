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
    Button

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
                    <TableCell>Action</TableCell>
                </TableRow>
            </TableHead>
            <TableBody>
                <TableRow>
                    <TableCell></TableCell>
                    <TableCell></TableCell>
                    <TableCell></TableCell>
                    <TableCell><Button color="error" variant="contained" onClick={() => {
      console.log("removed")
                    }}>Remove</Button></TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </TableContainer>)
}

export default EditUserRoles;
