import useTranslatedTitle from '@/hooks/useTranslatedTitle'
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/_authenticated/')({
  component: RouteComponent,
})

function RouteComponent() {
  useTranslatedTitle('events.title');

  return <div>Homepage (authentuicated)</div>
}
