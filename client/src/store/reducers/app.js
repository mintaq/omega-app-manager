import * as actionTypes from '../actions/actionTypes';
import { updateObject } from '../../shared/utilities';

const initialState = {
	app: null,
	loading: null,
	error: null,
};

const fetchAppStart = (state, action) => {
	return updateObject(state, {
		error: null,
		loading: true,
	});
};

const fetchAppSuccess = (state, action) => {
	return updateObject(state, {
		app: action.app,
		error: null,
		loading: false,
	});
};

const fetchAppFail = (state, action) => {
	return updateObject(state, {
		error: action.error,
		loading: false,
	});
};

const reducer = (state = initialState, action) => {
	switch (action.type) {
		case actionTypes.FETCH_APP_START:
			return fetchAppStart(state, action);
		case actionTypes.FETCH_APP_SUCCESS:
			return fetchAppSuccess(state, action);
		case actionTypes.FETCH_APP_FAIL:
			return fetchAppFail(state, action);
		default:
			return state;
	}
};

export default reducer;
