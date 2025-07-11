import { Content, Description, Overlay, Portal, Root, Title } from '@radix-ui/react-dialog';

type Props = {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
    title: string;
    description?: string;
    children?: React.ReactNode;
    actions?: React.ReactNode;
};

export default function Modal({ open, onOpenChange, title, description, actions, children }: Props) {
    return <Root open={open} onOpenChange={onOpenChange}>
        <Portal>
            <Overlay className='DialogOverlay' />
            <Content className='DialogContent'>
                <Title className='text-yellow-glow text-2xl font-bold'>{title}</Title>
                {
                    description && <Description hidden={description === title}>{description}</Description>
                }

                {children}

                {
                    actions && <div className='flex flex-row justify-end gap-2 mt-4'>
                        {actions}
                    </div>
                }
            </Content>
        </Portal>
    </Root>
}