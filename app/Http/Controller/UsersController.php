<?php

namespace App\Http\Controller;

use Slim\Http\Response;

class UsersController extends AbstractController
{
    public function index(): Response
    {
        $this->setTitle($this->app->getAuth()->getUserName());
        $this->setSubTitle('@' . $this->app->getAuth()->getUserLogin());

        return $this->view->render('users/index.blade.php', [
            'sshKeyUploaded' => file_exists('ssh_keys/'. $this->app->getAuth()->getUserLogin()),
        ]);
    }
    
    public function addkey(): Response
    {
        $this->setTitle(__('set_ssh_key'));
        
        $text = __('ssh_key_page_description');
        
        if ($this->request->isPost()) {
            $key = $this->p('key');
            $key = str_replace("\r\n", "\n", trim($key)) . "\n";
            $filename = 'ssh_keys/'. $this->app->getAuth()->getUserLogin();

            if ($key && file_put_contents($filename, $key) !== false) {
                chmod($filename, 0600);
                $text = __('ssh_key_saved_successfully');
            } else {
                $text = __('set_ssh_save_failed');
            }
        }
        
        return $this->view->render('users/addkey.blade.php', [
            'msg' => $text,
        ]);
    }

    public function committerInfo(): Response
    {
        $this->setTitle(__('set_committer'));

        if ($this->request->isPost()) {
            $name = $this->p('name');
            $email = $this->p('email');

            $pattern = "#[^a-zA-Z0-9@\s]*#";
            $name = preg_filter($pattern, '', $name);
            $email = preg_filter($pattern, '', $email);

            $user = $this->app->getAuth()->getUser();
            $user->setCommitAuthorName($name);
            $user->setCommitAuthorEmail($email);

            $user->save();

            return $this->response->withRedirect('/user');
        }

        return $this->view->render('users/setCommitterForm.blade.php');
    }

    public function accessToken(): Response
    {
        $this->setTitle(__('set_pat'));

        $text = __('pat_token_page_description');

        if ($this->request->isPost()) {
            $token = $this->p('token');
            $expirationDate = $this->p('expiration_date');
            $expirationDate = new \DateTimeImmutable($expirationDate);

            $user = $this->app->getAuth()->getUser();
            $user->setAccessToken($token);
            $user->setAccessTokenExpirationDate($expirationDate->format('Y-m-d H:i:s'));

            $user->save();

            return $this->response->withRedirect('/user');
        }

        return $this->view->render('users/accessToken.blade.php', [
            'msg' => $text,
        ]);
    }

    public function checkToken(): Response
    {
        $token = $this->p('token');

        $guthubResponse = $this->getGithubUserByToken($token);

        $headers = $this->getHeadersArray($guthubResponse);
        $expirationDate = $headers['github-authentication-token-expiration'];
        $tokenExpirationDate = new \DateTimeImmutable($expirationDate);

        $body = $this->getBody($guthubResponse);
        $body = json_decode( $body, true);

        $githubLogin = $body['login'];
        $githubName = $body['name'];

        return $this->app->json([
            'expiration_date' => $tokenExpirationDate->format('Y-m-d H:i:s'),
            'login' => $githubLogin,
            'name' => $githubName,
        ]);
    }

    private function getGithubUserByToken(string $token): string
    {
        $token = urlencode($token);

        $ch  = curl_init();
        $url = 'https://api.github.com/user';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $res = curl_exec($ch);
        curl_close($ch);

        return str_replace("\r", '', $res);
    }

    private function getHeadersArray(string $curlResponse): array
    {
        $res = str_replace("\r", '', $curlResponse);
        list($headers, $body) = explode("\n\n", $res);

        $headers = explode("\n", $headers);
        $parsedHeaders = [];

        foreach ($headers as $item) {
            if (str_contains($item, ':')) {
                list($key, $value) = explode(':', $item, 2);
                $parsedHeaders[ trim($key) ] = trim($value);
            }
        }

        return $parsedHeaders;
    }

    private function getBody(string $curlResponse): string
    {
        $res = str_replace("\r", '', $curlResponse);
        list($headers, $body) = explode("\n\n", $res);

        return trim($body);
    }
}
