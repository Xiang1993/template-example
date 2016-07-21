<?php
/** 
 * 模板引擎
 */
class CompileClass
{
    /**
     * 待编译的文件
     */
    private $template;

    /**
     * 需要替换的文本
     */
    private $content;

    /**
     * 编译后的文件
     */
    private $comfile;

    /**
     * 左定界符
     */
    private $left = '{';

    /**
     * 右定界符
     */
    private $right = '}';

    /**
     * 值栈
     */
    private $value = array();

    /**
     * 匹配规则
     */
    private $T_P = array();

    /**
     * 替换规则
     */
    private $T_R = array();

    /**
     * 构造函数
     */
    public function __construct($template, $compileFile, $config) 
    {
        $this->template = $template;
        $this->comfile = $compileFile;
        $this->content = file_get_contents($template);

        // 如果不支持原生PHP, 则修改开始和结束标签为特殊字符,让php解释引擎不解释
        if ($config['php_turn'] === false) {
            $this->T_P[] = "#<\?(=|php|)(.+?)\?>#is";
            $this->T_R[] = "&lt;?\\1\\2 ?&gt;";
        }

        /**
         * 用正则表达式实现变量标签
         */
        $this->T_P[] = "#\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}#";
        
        $this->T_R[] = "<?php echo \$this->value['\\1']; ?>";

        /**
         * 用正则表达式实现foreach标签
         */
        $this->T_P[] = "#\{(loop|foreach) \\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}#";
        $this->T_P[] = "#\{\/(loop|foreach|if)}#";
        $this->T_P[] = "#\{([K|V])}#";

        $this->T_R[] = "<?php foreach ((array)\$this->value['\\2'] as \$K => \$V) {?>";
        $this->T_R[] = "<?php } ?>";
        $this->T_R[] = "<?php echo \$\\1; ?>";

        /**
         * 用正则表达式实现if...else标签
         */
        $this->T_P[] = "#\{if (.*?)\}#";
        $this->T_P[] = "#\{(else if|elseif) (.*?)\}#";
        $this->T_P[] = "#\{else\}#";

        $this->T_R[] = '<?php if(\\1) {?>';
        $this->T_R[] = '<?php } else if(\\2) { ?>';
        $this->T_R[] = '<?php } else { ?>';

        /**
         * 用正则表达式去掉注释
         */
        $this->T_P[] = "#\{(\#|\*)(.*?)(\#|\*)\}#";
        $this->T_R[] = "";

        /**
         * 用正则表达式解析Javacript标签
         */
        $this->T_P[] = "#\{\!(.*?)\!\}#";
        $this->T_R[] = "<script src=\\1" . "?t=" . time() . "></script>";
    }

    public function compile() 
    {
        $this->content = preg_replace($this->T_P, $this->T_R, $this->content);
        file_put_contents($this->comfile, $this->content);
    }

    public function __set($name, $value)
    {
        $this->name = $value;
    }

    public function __get($name)
    {
        return $this->name;
    }
}