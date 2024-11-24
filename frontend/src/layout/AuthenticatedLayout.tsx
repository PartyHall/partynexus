import { Content, Footer, Header } from 'antd/es/layout/layout';
import { Flex, Layout, Menu, Typography } from 'antd';
import { IconCalendar, IconMenu, IconMicrophone2, IconUser } from '@tabler/icons-react';
import { Outlet, useNavigate } from 'react-router-dom';
import PhLogo from '../assets/ph_logo_sd.webp';
import { useAuth } from '../hooks/auth';
import { useEffect } from 'react';
import { useTranslation } from 'react-i18next';

export default function AuthenticatedLayout() {
  const { isLoggedIn, api } = useAuth();
  const { t } = useTranslation();
  const navigate = useNavigate();

  const menuItems = [
    { key: '/', label: t('menu.events'), icon: <IconCalendar size={20} /> },
    { 'key': '/karaoke', label: t('menu.karaoke'), icon: <IconMicrophone2 size={20} /> },
    { key: '/me', label: t('menu.my_account'), icon: <IconUser size={20} /> },
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
          style={{ display: 'block', height: '80%', cursor: 'pointer' }}
          alt="PartyHall logo"
          onClick={() => navigate('/')}
        />
        <Menu
          theme="dark"
          mode="horizontal"
          items={menuItems}
          selectedKeys={['/me']} // We want to highlight the acount and nothing else so lets just reuse this
          style={{ flex: 1, minWidth: 0, justifyContent: 'end', gap: 8 }}
          overflowedIndicator={<IconMenu size={20} />}
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

      <Footer style={{ textAlign: 'center', padding: '.25em' }}>
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
