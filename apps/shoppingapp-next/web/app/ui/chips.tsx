export function Chips({ items }: { items: string[] }) {
  if (items.length === 0) return null;
  return (
    <span className="flex flex-wrap gap-1">
      {items.map((i) => (
        <span key={i} className="rounded border px-1.5 text-xs text-gray-600">{i}</span>
      ))}
    </span>
  );
}
