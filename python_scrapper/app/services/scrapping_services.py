from app.clients.meta_clients import MetaClient


class ScrappingService:

    def __init__(self):
        self.meta_client = MetaClient()

    def process(self, credentials):

        result = self.meta_client.fetch_period_insights(
            credentials['instagram_business_id'],
            credentials['access_token']
        )

        if not result or "metrics" not in result:
            raise Exception("Invalid Meta response")

        return self.normalize(result)

    def normalize(self, result):

        metrics = result.get("metrics", {})
        since = result.get("since")
        until = result.get("until")

        # 🔥 DEFAULT METRICS (SINGLE SOURCE OF TRUTH)
        default_metrics = {
            "reach": 0,
            "views": 0,
            "likes": 0,
            "comments": 0,
            "follows_and_unfollows": 0
        }

        final_metrics = {}

        for key in default_metrics:
            value = metrics.get(key, 0)

            try:
                value = int(value)
            except:
                value = 0

            final_metrics[key] = value

        # 🔥 DETECT EMPTY
        is_empty = all(v == 0 for v in final_metrics.values())

        result_payload = {
            "metrics": final_metrics,
            "since": since,
            "until": until
        }

        if is_empty:
            result_payload["note"] = "empty_insights"

        return result_payload