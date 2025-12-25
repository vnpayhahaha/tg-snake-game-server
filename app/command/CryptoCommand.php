<?php

namespace app\command;

use app\lib\helper\CryptoHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * TRON热钱包私钥加密管理命令
 *
 * 使用方法：
 * 1. 生成加密密钥: php webman crypto:key
 * 2. 加密私钥: php webman crypto:encrypt-key <private_key>
 * 3. 解密私钥: php webman crypto:decrypt-key <encrypted_key>
 */
class CryptoCommand extends Command
{
    protected static $defaultName = 'crypto';
    protected static $defaultDescription = 'TRON热钱包私钥加密管理';

    protected function configure()
    {
        $this->addOption('generate-key', 'g', InputOption::VALUE_NONE, '生成新的加密密钥')
            ->addOption('encrypt', 'e', InputOption::VALUE_REQUIRED, '加密私钥')
            ->addOption('decrypt', 'd', InputOption::VALUE_REQUIRED, '解密私钥（仅用于验证）')
            ->addOption('test', 't', InputOption::VALUE_NONE, '测试加密功能');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('generate-key')) {
            return $this->generateKey($input, $output);
        }

        if ($input->getOption('encrypt')) {
            return $this->encryptKey($input, $output);
        }

        if ($input->getOption('decrypt')) {
            return $this->decryptKey($input, $output);
        }

        if ($input->getOption('test')) {
            return $this->testEncryption($input, $output);
        }

        // 显示帮助信息
        $output->writeln('');
        $output->writeln('<info>TRON热钱包私钥加密管理工具</info>');
        $output->writeln('');
        $output->writeln('<comment>使用方法:</comment>');
        $output->writeln('  1. 生成加密密钥: <info>php webman crypto --generate-key</info>');
        $output->writeln('  2. 加密私钥:     <info>php webman crypto --encrypt=YOUR_PRIVATE_KEY</info>');
        $output->writeln('  3. 解密验证:     <info>php webman crypto --decrypt=ENCRYPTED_KEY</info>');
        $output->writeln('  4. 测试加密:     <info>php webman crypto --test</info>');
        $output->writeln('');

        return Command::SUCCESS;
    }

    /**
     * 生成加密密钥
     */
    protected function generateKey(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('');
        $output->writeln('<info>正在生成加密密钥...</info>');

        $key = CryptoHelper::generateKey();

        $output->writeln('');
        $output->writeln('<comment>===========================================</comment>');
        $output->writeln('<info>加密密钥生成成功！</info>');
        $output->writeln('<comment>===========================================</comment>');
        $output->writeln('');
        $output->writeln('<fg=yellow>请将以下密钥添加到 .env 文件中：</>');
        $output->writeln('');
        $output->writeln("<fg=green>APP_ENCRYPTION_KEY={$key}</>");
        $output->writeln('');
        $output->writeln('<fg=red>⚠️ 重要提示：</>');
        $output->writeln('<fg=red>1. 请妥善保管此密钥，丢失后无法解密已加密的数据</>');
        $output->writeln('<fg=red>2. 切勿将密钥提交到版本控制系统</>');
        $output->writeln('<fg=red>3. 生产环境应使用独立的密钥</>');
        $output->writeln('');

        return Command::SUCCESS;
    }

    /**
     * 加密私钥
     */
    protected function encryptKey(InputInterface $input, OutputInterface $output): int
    {
        $privateKey = $input->getOption('encrypt');

        if (empty($privateKey)) {
            $output->writeln('<error>错误: 私钥不能为空</error>');
            return Command::FAILURE;
        }

        // 验证私钥格式（64位十六进制）
        if (strlen($privateKey) !== 64 || !ctype_xdigit($privateKey)) {
            $output->writeln('<error>错误: 无效的私钥格式（应为64位十六进制字符串）</error>');
            return Command::FAILURE;
        }

        // 检查加密密钥是否配置
        if (!CryptoHelper::isKeyConfigured()) {
            $output->writeln('<error>错误: 加密密钥未配置</error>');
            $output->writeln('<comment>请先使用 --generate-key 生成加密密钥并配置到.env文件</comment>');
            return Command::FAILURE;
        }

        try {
            $output->writeln('');
            $output->writeln('<info>正在加密私钥...</info>');

            $encrypted = CryptoHelper::encrypt($privateKey);

            $output->writeln('');
            $output->writeln('<comment>===========================================</comment>');
            $output->writeln('<info>私钥加密成功！</info>');
            $output->writeln('<comment>===========================================</comment>');
            $output->writeln('');
            $output->writeln('<fg=yellow>加密后的私钥（请存入数据库 hot_wallet_private_key 字段）：</>');
            $output->writeln('');
            $output->writeln("<fg=green>{$encrypted}</>");
            $output->writeln('');
            $output->writeln('<fg=red>⚠️ 安全提示：</>');
            $output->writeln('<fg=red>1. 加密后的数据存入数据库</>');
            $output->writeln('<fg=red>2. 原始私钥应立即销毁</>');
            $output->writeln('<fg=red>3. 切勿在日志中记录原始私钥</>');
            $output->writeln('');

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln("<error>加密失败: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }

    /**
     * 解密私钥（仅用于验证）
     */
    protected function decryptKey(InputInterface $input, OutputInterface $output): int
    {
        $encryptedKey = $input->getOption('decrypt');

        if (empty($encryptedKey)) {
            $output->writeln('<error>错误: 加密私钥不能为空</error>');
            return Command::FAILURE;
        }

        // 检查加密密钥是否配置
        if (!CryptoHelper::isKeyConfigured()) {
            $output->writeln('<error>错误: 加密密钥未配置</error>');
            return Command::FAILURE;
        }

        try {
            $output->writeln('');
            $output->writeln('<info>正在解密私钥...</info>');

            $decrypted = CryptoHelper::decrypt($encryptedKey);

            $output->writeln('');
            $output->writeln('<info>解密成功！私钥格式验证：</info>');
            $output->writeln("  长度: " . strlen($decrypted) . " 字符");
            $output->writeln("  格式: " . (ctype_xdigit($decrypted) ? '✓ 有效十六进制' : '✗ 无效格式'));
            $output->writeln('');
            $output->writeln('<fg=yellow>前8位: </>' . substr($decrypted, 0, 8) . '...');
            $output->writeln('<fg=yellow>后8位: </>...' . substr($decrypted, -8));
            $output->writeln('');
            $output->writeln('<fg=red>⚠️ 出于安全考虑，不显示完整私钥</>');
            $output->writeln('');

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln("<error>解密失败: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }

    /**
     * 测试加密功能
     */
    protected function testEncryption(InputInterface $input, OutputInterface $output): int
    {
        // 检查加密密钥是否配置
        if (!CryptoHelper::isKeyConfigured()) {
            $output->writeln('<error>错误: 加密密钥未配置</error>');
            $output->writeln('<comment>请先使用 --generate-key 生成加密密钥</comment>');
            return Command::FAILURE;
        }

        $output->writeln('');
        $output->writeln('<info>开始测试加密功能...</info>');
        $output->writeln('');

        // 生成测试私钥
        $testKey = bin2hex(random_bytes(32));
        $output->writeln("  测试私钥: {$testKey}");

        try {
            // 加密
            $encrypted = CryptoHelper::encrypt($testKey);
            $output->writeln("  <fg=green>✓</> 加密成功");

            // 解密
            $decrypted = CryptoHelper::decrypt($encrypted);
            $output->writeln("  <fg=green>✓</> 解密成功");

            // 验证
            if ($decrypted === $testKey) {
                $output->writeln("  <fg=green>✓</> 数据一致性验证通过");
                $output->writeln('');
                $output->writeln('<info>✓ 加密功能测试通过！</info>');
                return Command::SUCCESS;
            } else {
                $output->writeln("  <fg=red>✗</> 数据不一致");
                return Command::FAILURE;
            }

        } catch (\Throwable $e) {
            $output->writeln("<error>测试失败: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
