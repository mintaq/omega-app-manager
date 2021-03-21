export const authPath = 'http://localhost:11000/omega-app-manager/authenticate.php';
export const servicePath = 'http://localhost:11000/omega-app-manager/services.php';
export const axiosAuthConfig = {
	headers: {
		Authorization: 'Bearer ' + window.localStorage.getItem('token'),
	},
};
