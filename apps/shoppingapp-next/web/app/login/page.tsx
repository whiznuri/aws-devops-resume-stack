import { AuthForm } from "@/app/ui/auth-form";
import { Notice } from "@/app/ui/notice";
import { safeNext } from "@/lib/nav";

export default async function LoginPage({ searchParams }: PageProps<"/login">) {
  const { notice, next } = await searchParams;

  return (
    <main className="mx-auto max-w-sm p-10">
      <Notice code={notice} />
      <h1 className="mb-6 text-2xl font-bold">เข้าสู่ระบบ</h1>
      <AuthForm mode="login" next={safeNext(next)} />
    </main>
  );
}
