import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../store/actions';
import { Layout, Menu, Breadcrumb } from 'antd';
import { PieChartOutlined, FileOutlined, TeamOutlined, UserOutlined } from '@ant-design/icons';
import DataTable from '../../components/DataTable/DataTable';
import './Dashboard.css';

const { Header, Content, Sider } = Layout;
const { SubMenu } = Menu;

class Dashboard extends React.Component {
	state = {
		collapsed: false,
	};

	onCollapse = collapsed => {
		console.log(collapsed);
		this.setState({ collapsed });
	};

	render() {
		const { collapsed } = this.state;
		return (
			<Layout style={{ minHeight: '100vh' }}>
				<Sider collapsible collapsed={collapsed} onCollapse={this.onCollapse} position="fixed">
					<div className="logoz" />
					<Menu theme="dark" defaultSelectedKeys={['1']} mode="inline">
						<Menu.Item key="1" icon={<PieChartOutlined />} onClick={() => console.log('op1')}>
							Dashboard
						</Menu.Item>
						<SubMenu key="sub1" icon={<UserOutlined />} title="Apps">
							<Menu.Item key="3" onClick={() => this.props.onFetchApp('1')}>
								Google Feed Pro
							</Menu.Item>
							<Menu.Item key="4">Bill</Menu.Item>
							<Menu.Item key="5">Alex</Menu.Item>
						</SubMenu>
						<SubMenu key="sub2" icon={<TeamOutlined />} title="Team">
							<Menu.Item key="6">Team 1</Menu.Item>
							<Menu.Item key="8">Team 2</Menu.Item>
						</SubMenu>
						<Menu.Item key="9" icon={<FileOutlined />}>
							Files
						</Menu.Item>
					</Menu>
				</Sider>
				<Layout className="site-layout">
					<Header className="site-layout-background" style={{ padding: 0 }} />
					<Content style={{ margin: '0 16px' }}>
						<Breadcrumb style={{ margin: '16px 0' }}>
							<Breadcrumb.Item>User</Breadcrumb.Item>
							<Breadcrumb.Item>Bill</Breadcrumb.Item>
						</Breadcrumb>
						<div className="site-layout-background" style={{ padding: 24, minHeight: 360 }}>
							<DataTable />
						</div>
					</Content>
				</Layout>
			</Layout>
		);
	}
}

const mapStateToProps = state => {
	return {
		app: state.app.app,
	};
};

const mapDispatchToProps = dispatch => {
	return {
		onFetchApp: appId => dispatch(actions.fetchAppById(appId)),
	};
};

export default connect(mapStateToProps, mapDispatchToProps)(Dashboard);
