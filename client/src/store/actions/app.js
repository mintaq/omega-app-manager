import * as actionTypes from './actionTypes';
import axios from 'axios';
import { servicePath, axiosAuthConfig } from '../../config/config';

export const fetchAppStart = () => {
	return {
		type: actionTypes.FETCH_APP_START,
	};
};

export const fetchAppSuccess = app => {
	return {
		type: actionTypes.FETCH_APP_SUCCESS,
		app,
	};
};

export const fetchAppFail = error => {
	return {
		type: actionTypes.FETCH_APP_FAIL,
		error,
	};
};

export const fetchAppById = appId => {
	return dispatch => {
		dispatch(fetchAppStart());
		axios
			.get(servicePath, {
				params: { appId, action: 'getAppDataById' },
				headers: {
					Authorization: 'Bearer ' + window.localStorage.getItem('token'),
				},
			})
			.then(response => {
				console.log(response);
				dispatch(fetchAppSuccess(response.data));
			})
			.catch(err => {
				console.error(err);
				dispatch(fetchAppFail(err));
			});
	};
};
