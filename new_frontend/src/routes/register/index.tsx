import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/register/')({
  component: RouteComponent,
})

function TestGlow({ col }: { col: string }) {
  return <div className={`box-${col}-glow p-3 m-4`}>
    <span className={`text-${col}-glow`}>Test Glow {col}</span>
  </div>
}

function RouteComponent() {
  return <div>
    {
      [
        'purple',
        'blue',

        'green',
        'lemon-green',

        'yellow',
        'gold',
        
        'red',
        'pink',
        'white',
      ].map((col) => (
        <TestGlow key={col} col={col} />
      ))
    }
  </div>
}
