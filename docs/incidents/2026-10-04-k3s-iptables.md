# Incident: public site unreachable after a k3s experiment (4 Oct 2026)



## Summary

While trying k3s and ArgoCD on the same t3.micro (914 MiB RAM) that serves the live site, I stopped the Docker containers to free memory and installed k3s. After the experiment, the containers were back and healthy, but ports 80 and 443 timed out from outside. Ports published directly (for example 8080) were reachable. Running the official k3s cleanup script and restarting Docker restored the site.

## Impact

- The public site (diffan.dev, lovelypet.diffan.dev, grafana.diffan.dev) was unreachable on ports 80/443 for roughly three hours. This is an estimate from terminal logs and screenshots; the exact start is not recorded.
- No data loss was observed. The MySQL volume was untouched.

## Timeline (UTC, approximate)

| Time | Event |
|---|---|
| ≈13:40 | Stopped all eight containers to free memory for the experiment |
| ≈13:50 | Installed k3s with the official script; node `Ready` after a few minutes |
| 13:58 | Applied the `web-resume` Deployment and Service; `ErrImagePull`. Events showed `pull access denied`; the Docker Hub username in the manifest was wrong. Fixed in commit `d30cb87`; pod `Running 1/1` |
| ≈14:00–16:00 | Installed ArgoCD. API server timeouts, repeated k3s restarts, ArgoCD pods restarting 13 to 18 times. The Application resource never reached `Synced` |
| ≈16:00 | Rebooted the instance. Deleted the `argocd` namespace, stopped and disabled k3s |
| ≈16:25 | `docker compose up -d` brought Nginx and Grafana back. All eight containers `Up`. `curl localhost` returned 301 (HTTP to HTTPS redirect) and HTTPS returned 200 locally |
| ≈16:29 | From the desktop browser, `http://<elastic-ip>:8080` loaded, while `https://diffan.dev` timed out |
| ≈16:40 | Ran `k3s-killall.sh`, restarted Docker, `docker compose up -d` |
| 16:42 | `https://diffan.dev` loaded normally |

## Diagnosis

I narrowed the problem layer by layer:

1. **Containers:** all `Up`, Nginx config test passed. Not an application problem.
2. **From the host:** `curl localhost` redirected and served as expected.
3. **From outside, direct port:** `:8080` worked, so the instance, Security Group and network path were fine.
4. **From outside, ports 80/443:** timeout (silence, not a refusal or a 404). That left the host's network rules between the interface and the process behind 80/443.

## Probable root cause

k3s bundles Traefik and its service load balancer, which publish ports 80 and 443 on the host through iptables rules, plus CNI and flannel interfaces. Stopping the `k3s` systemd service does not remove those rules. They kept intercepting traffic to 80/443 before it reached `docker-proxy`.

This is inferred from the symptoms and from the fix (the cleanup script removes `KUBE-*`, `CNI-*` and flannel rules and interfaces). I did not capture packets or dump the iptables chains before the cleanup, so it is not confirmed.

## Resolution

```bash
sudo /usr/local/bin/k3s-killall.sh   # removes k3s containers, interfaces and its iptables rules
sudo systemctl restart docker        # makes Docker rebuild its own rules
docker compose up -d
```

Verified from outside the instance (a browser on a different network) that the site loaded over HTTPS.

## Contributing factors

- The experiment ran on the host that serves the live site, with no spare memory (about 80% RAM used before starting).
- All containers were stopped by hand and only partly restarted by the CI deploy, which recreates only the two app services, so Nginx stayed down.
- k3s was left `enabled` after the first failed attempts and came back after a reboot.
- Monitoring runs on the same host and checks only from inside, so nothing alerted on external unreachability. I found out by opening the site in a browser.

## What went well

- Layered diagnosis found the problem area quickly: container, localhost, direct port, then 80/443.
- The direct application port (8080) stayed reachable and was usable as a fallback.
- The rollback to Docker Compose was kept simple, and the data volume was never touched.

## What went wrong

- Experimenting on the live host.
- Assuming that stopping a service removes its network state.
- No outside-in health check.
- No packet or iptables capture before cleanup, so the cause stays an inference.

## Action items

- [ ] Run Kubernetes experiments on a separate instance or locally (k3d)
- [ ] Add an external uptime check that fails when 80/443 are unreachable
- [ ] Before cleaning up network state, save `iptables -t nat -S` for the record
- [ ] Remove k3s completely with `k3s-uninstall.sh` and reclaim disk (usage rose from about 34% to about 50%)
- [ ] Make Nginx re-resolve upstream names at runtime so recreated containers no longer need a manual reload
- [ ] Add a short runbook entry: "site unreachable on 80/443 but app ports work → check host firewall/NAT rules"
