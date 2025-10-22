import { HttpError } from "@/api/http_error";
import getUserAuthenticationLogs from "@/api/users/auth_logs";
import type { User, UserAuthLog } from "@/types/user";
import { useQuery } from "@tanstack/react-query";
import { useTranslation } from "react-i18next";
import dayjs from "dayjs";

type Props = {
  user: User;
};

export default function UserAuthenticationLogs({ user }: Props) {
  const { t } = useTranslation();

  const page = 1; // Maybe at some point we can add pagination

  const { isPending, isError, error, data } = useQuery({
    queryKey: ["user_auth_logs", user.id, page],
    queryFn: async () => await getUserAuthenticationLogs(user.id, page),
  });

  return (
    <div className="overflow-auto">
      {isPending && <p>{t("generic.loading")}</p>}

      {isError && (
        <p>
          {t("generic.error.generic")}:{" "}
          {error instanceof HttpError ? t(error.message) : String(error)}
        </p>
      )}

      {data && data.member.length > 0 && (
        <ul>
          {data.member.map((log: UserAuthLog) => (
            <li className="bg-synthbg-900 rounded-md p-2 m-1" key={log.id}>
              <p>
                {t("generic.date")}: {dayjs(log.authedAt).format("L - LT")}
              </p>
              <p>
                {t("generic.ip")}: {log.ip}
              </p>
            </li>
          ))}
        </ul>
      )}

      {data && data.member.length === 0 && (
        <p className="text-center text-primary-100">
          {t("admin.users.auth_log.empty")}
        </p>
      )}
    </div>
  );
}
