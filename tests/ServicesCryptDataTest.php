<?php
namespace Robsonala\CryptoUser\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Robsonala\CryptoUser\Services\CryptoUser;
use Robsonala\CryptoUser\Exceptions\CryptoUserException;
use Robsonala\CryptoUser\Traits\CryptData;
use Robsonala\CryptoUser\Test\Models\Todo;

class ServicesCryptDataTest extends TestCase
{
    /** @test */
    public function i_can_encrypt_text()
    {
        $key = CryptoUser::setSessionPassphrase();
        $plain = 'Lorem ipsum dolor sit amet ' . uniqid();

        $todo = new Todo([
            'title' => $plain,
            'text' => $plain
        ]);
        $todo->save();

        $items = DB::table('todo')->where('id', $todo->id)->first();
        
        $this->assertEquals($plain, $items->title);
        $this->assertNotEquals($plain, $items->text);
        
    }

    /** @test */
    public function i_can_decrypt_text()
    {
        $key = CryptoUser::setSessionPassphrase();
        $plain = 'Lorem ipsum dolor sit amet ' . uniqid();

        $todo = new Todo([
            'title' => $plain,
            'text' => $plain
        ]);
        $todo->save();
        
        $this->assertEquals($plain, $todo->title);
        $this->assertEquals($plain, $todo->text);
    }

    /** @test */
    public function fail_encrypt_when_there_is_no_key()
    {
        $this->killPassphraseSession();

        $plain = 'Lorem ipsum dolor sit amet ' . uniqid();

        try {
            $todo = new Todo([
                'title' => $plain,
                'text' => $plain
            ]);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function fail_decrypt_when_there_is_no_key()
    {
        $key = CryptoUser::setSessionPassphrase();
        $plain = 'Lorem ipsum dolor sit amet ' . uniqid();

        $todo = new Todo([
            'title' => $plain,
            'text' => $plain
        ]);
        $todo->save();

        $todo->title;
        $todo->text;

        $this->killPassphraseSession();

        $todo->title;
        try {
            $todo->text;

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function fail_decrypt_using_wrong_key()
    {
        $key = CryptoUser::setSessionPassphrase();
        $plain = 'Lorem ipsum dolor sit amet ' . uniqid();

        $todo = new Todo([
            'title' => $plain,
            'text' => $plain
        ]);
        $todo->save();

        // Generate a new key
        CryptoUser::setSessionPassphrase();

        $todo->title;
        try {
            $todo->text;

            throw new \Exception('This test should fail');
        } catch (DecryptException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function model_will_fail_if_i_set_trait_but_not_add_attr()
    {
        $model = (new class extends Model {
            use CryptData;

            protected $fillable = ['title'];
        });

        try {
            $model->title = 'Lorem ipsum dolor sit amet ' . uniqid();

            throw new \Exception('This test should fail');
        } catch (\ErrorException $e) {
            $this->assertContains('Undefined property', $e->getMessage());
            $this->assertContains('$crypt_attributes', $e->getMessage());
        }
    }
}