import { AudioOutlined, CalendarOutlined, MenuOutlined, UserOutlined } from '@ant-design/icons';
import { Content, Footer, Header } from 'antd/es/layout/layout';
import { Flex, Layout, Menu, Typography } from 'antd';
import { Outlet, useNavigate } from 'react-router-dom';
import React, { useEffect } from 'react';
import PhLogo from '../assets/ph_logo_sd.webp';
import { useAuth } from '../hooks/auth';
import { useTranslation } from 'react-i18next';

export default function AuthenticatedLayout() {
  const { isLoggedIn, api } = useAuth();
  const { t } = useTranslation();
  const navigate = useNavigate();

  const menuItems = [
    { key: '/', label: t('menu.events'), icon: <CalendarOutlined /> },
    { 'key': '/karaoke', label: t('menu.karaoke'), icon: <AudioOutlined /> },
    { key: '/me', label: t('menu.my_account'), icon: <UserOutlined /> },
  ];

  useEffect(() => {
    if (!isLoggedIn()) {
      navigate('/login');
      return;
    }
  }, [api.token]);

  return (
    <Layout>
      <Header
        style={{
          position: 'sticky',
          top: 0,
          zIndex: 1,
          width: '100%',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
        }}
      >
        <img
          src={PhLogo}
          style={{ display: 'block', height: '80%' }}
          alt="PartyHall logo"
        />
        <Menu
          theme="dark"
          mode="horizontal"
          items={menuItems}
          selectedKeys={['/me']} // We want to highlight the acount and nothing else so lets just reuse this
          style={{ flex: 1, minWidth: 0, justifyContent: 'end', gap: 8 }}
          overflowedIndicator={<MenuOutlined />}
          onClick={(menuitem) => {
            if (menuitem) {
              navigate(menuitem.key);
            }
          }}
        />
      </Header>

      <Content>
        <Flex vertical gap={16} flex={1} style={{ maxWidth: '800px', width: '100%', height: '100%', overflowY: 'auto', margin: 'auto' }}>
          <Outlet />
        </Flex>
      </Content>

      <Footer style={{ textAlign: 'center' }}>
        <Typography>
          PartyHall -{' '}
          <a
            href="https://github.com/partyhall/PartyHall"
            target="_blank"
            rel="noopener noreferrer"
          >
            Github
          </a>
        </Typography>
      </Footer>
    </Layout>
  );
}
