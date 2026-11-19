import { AuthForm } from "@/app/ui/auth-form";
import { safeNext } from "@/lib/nav";

export default async function RegisterPage({ searchParams }: PageProps<"/register">) {
  const { next } = await searchParams;

  return (
    <main className="mx-auto max-w-sm p-10">
      <h1 className="mb-6 text-2xl font-bold">สมัครสมาชิก</h1>
      <AuthForm mode="register" next={safeNext(next)} />
    </main>
  );
}
