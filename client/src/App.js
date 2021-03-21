import React from 'react';
import { Route, Switch, withRouter, Redirect } from 'react-router-dom';
import { connect } from 'react-redux';

import { Layout } from 'antd';
import { Content } from 'antd/lib/layout/layout';
import * as actions from './store/actions/index';
import 'antd/dist/antd.css'; // or 'antd/dist/antd.less'
import './App.css';

import Auth from './containers/Auth/Auth';
import Dashboard from './containers/Dashboard/Dashboard';

class App extends React.Component {
	componentDidMount() {
		this.props.onTryAutoSignup();
	}

	render() {
		let routes = (
			<div className="App__Auth_layout_wrapper">
				<Layout className="App__Auth_layout">
					<Switch>
						<Route path="/auth" component={Auth} />
						<Redirect to="/auth" />
					</Switch>
				</Layout>
			</div>
		);

		if (this.props.isAuthenticated) {
			routes = (
				<Layout className="App__Dashboard_layout">
					<Switch>
						<Route path="/" exact component={Dashboard} />
						<Redirect to="/" />
					</Switch>
				</Layout>
			);
		}

		return <div className="App">{routes}</div>;
	}
}
const mapStateToProps = state => {
	return {
		isAuthenticated: state.auth.token !== null,
	};
};

const mapDispatchToProps = dispatch => {
	return {
		onTryAutoSignup: () => dispatch(actions.authCheckState()),
	};
};

export default withRouter(connect(mapStateToProps, mapDispatchToProps)(App));
