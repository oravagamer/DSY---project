import OravixSecurity from "./oravixSecurity.js";
import {backendUrl} from "../settings.js";

const oravixSecurity = new OravixSecurity(backendUrl + "/security");
export default oravixSecurity;