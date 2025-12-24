<?php

namespace app\command;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateModel extends Command
{
    protected static $defaultName = 'generate:model';
    protected static $defaultDescription = 'generate model';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'Name description');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output):  int
    {
        // 初始化 Illuminate Database Capsule
        $capsule = new Capsule;
        $capsule->addConnection(config('database.connections.mysql'));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // 获取所有表名
        $tables = $capsule->getConnection()->getSchemaBuilder()->getTables();
        var_dump('获取所有表名==',$tables);
        // 生成 Model 文件
        foreach ($tables as $table) {
            $table = $table['name'];
            $modelName = Str::studly($table);
            $filePath = app_path() . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Model'.$modelName . '.php';
            // 检查文件是否存在
            if (!is_file($filePath)) {
                // 如果不存在，则生成 Model 文件
                $this->generateModelFile($capsule, $table, $modelName, $filePath);
                $output->writeln("Model file generated: $modelName");
            } else {
                $output->writeln("Model file already exists: $modelName");
            }
        }

        $output->writeln('All model files generated successfully.');
        return 0;
    }


    protected function generateModelFile($capsule, $table, $modelName, $filePath)
    {
        // $capsule->getConnection()->select 查询表所有字段名及类型，创建字段属性 eg: * @property int \$id 主键
        $column_fields = [];


        $annotations = '/**'.PHP_EOL;
        $comments = [];
        $columns = $capsule->getConnection()->select("SHOW FULL COLUMNS FROM $table;");
        $pri_field_name = 'id';
        foreach ($columns as $column) {
            $column_fields[] = $column->Field;

            $fieldName = $column->Field;
            $type = $column->Type;
            $comment = $column->Comment ?? '';
            // 转换MySQL类型为PHP类型
            $phpType = 'mixed'; // 默认类型
            // 简单的类型映射
            if (strpos($type, 'int') !== false) {
                $phpType = 'int';
            } elseif (strpos($type, 'varchar') !== false ||
                strpos($type, 'text') !== false ||
                strpos($type, 'char') !== false) {
                $phpType = 'string';
            } elseif (strpos($type, 'decimal') !== false ||
                strpos($type, 'float') !== false ||
                strpos($type, 'double') !== false) {
                $phpType = 'float';
            } elseif (strpos($type, 'date') !== false ||
                strpos($type, 'time') !== false ||
                strpos($type, 'datetime') !== false ||
                strpos($type, 'timestamp') !== false) {
                $phpType = '\\Carbon\\Carbon';
            } elseif (strpos($type, 'tinyint(1)') !== false) {
                $phpType = 'bool';
            }
            $commentLine = "* @property {$phpType} \${$fieldName}";

            if ($column->Key === 'PRI') {
                $commentLine .= ' 主键';
                $pri_field_name = $fieldName;
            }
            if (!empty($comment)) {
                $commentLine .= ' ' . $comment;
            }

            $comments[] = $commentLine;
        }

        $annotations .= implode("\n", $comments).PHP_EOL.'*/';
        $column_fields = array_diff($column_fields, ['id']);
        $columns = '[' . PHP_EOL . '        \'' . implode('\',' . PHP_EOL . '        \'', $column_fields) . '\'' . PHP_EOL . '    ]';

        $content = <<<EOF
<?php

namespace app\model;

$annotations
final class Model$modelName extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected \$table = '$table';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected \$primaryKey = '$pri_field_name';
    
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected \$fillable = $columns;
}

EOF;

        file_put_contents($filePath, $content);
    }
}
