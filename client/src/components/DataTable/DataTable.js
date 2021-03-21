import React from 'react';
import { connect } from 'react-redux';
import { Table } from 'antd';

class DataTable extends React.Component {
	columns = [
		{
			title: 'Shop',
			dataIndex: 'shop',
			key: 'shop',
		},
		{
			title: 'Installed date',
			dataIndex: 'date_installed',
			key: 'date_installed',
		},
		{
			title: 'Phone',
			dataIndex: 'phone',
			key: 'phone',
		},
		{
			title: 'Country',
			dataIndex: 'country',
			key: 'country',
		},
		{
			title: 'Email',
			dataIndex: 'email',
			key: 'email',
		},
		{
			title: 'Enable App',
			dataIndex: 'enable',
			key: 'enable',
		},
		{
			title: 'Plan',
			dataIndex: 'plan',
			key: 'plan',
		},
		{
			title: 'Note',
			dataIndex: 'note',
			key: 'note',
		},
	];

	render() {
		let formatAppData = this.props.app.map(appData => {
			return {
				key: appData.id,
				shop: appData.shop,
				date_installed: appData.date_installed,
				phone: appData.phone,
				country: appData.country,
				email: appData.email_shop,
				status: appData.status,
				plan: appData.plan_name,
				note: appData.note,
			};
		});

		return <Table dataSource={formatAppData} columns={this.columns}></Table>;
	}
}

const mapStateToProps = state => {
	return {
		app: state.app.app,
	};
};

const mapDispatchToProps = dispatch => {};

export default connect(mapStateToProps, mapDispatchToProps)(DataTable);
